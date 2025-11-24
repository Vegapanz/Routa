<?php
/**
 * Pure PHP WebSocket Server - Optimized & Lightweight
 * No external dependencies - Uses native PHP sockets
 * Runs on port 8080 to avoid conflicts with Apache
 */

// Prevent timeout
set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '256M');

// Load database config
require_once __DIR__ . '/../php/config.php';

// Server configuration
define('WS_HOST', '0.0.0.0');
define('WS_PORT', 8080);
define('MAX_CLIENTS', 1000);
define('HEARTBEAT_INTERVAL', 30);

class WebSocketServer {
    private $master;
    private $sockets = [];
    private $clients = [];
    private $lastHeartbeat = 0;
    private $db;
    
    public function __construct($host, $port) {
        // Create socket
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->master, $host, $port);
        socket_listen($this->master, MAX_CLIENTS);
        socket_set_nonblock($this->master);
        
        $this->sockets[] = $this->master;
        
        // Database connection (mysqli for better performance)
        $this->db = new mysqli('localhost', 'root', '', 'routa_db');
        $this->db->set_charset('utf8mb4');
        
        $this->log("WebSocket server started on ws://{$host}:{$port}");
        $this->log("Memory usage: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB");
    }
    
    public function run() {
        while (true) {
            $read = $this->sockets;
            $write = null;
            $except = null;
            
            // Non-blocking select with 200ms timeout (prevents CPU spike)
            $changed = @socket_select($read, $write, $except, 0, 200000);
            
            if ($changed === false) {
                break;
            }
            
            // Handle new connections
            if (in_array($this->master, $read)) {
                $client = socket_accept($this->master);
                if ($client !== false) {
                    socket_set_nonblock($client);
                    $this->sockets[] = $client;
                    $client_id = spl_object_id($client);
                    $this->clients[$client_id] = [
                        'socket' => $client,
                        'handshake' => false,
                        'user_id' => null,
                        'role' => null,
                        'last_ping' => time()
                    ];
                }
                unset($read[array_search($this->master, $read)]);
            }
            
            // Handle client messages
            foreach ($read as $socket) {
                $client_id = spl_object_id($socket);
                $data = @socket_read($socket, 4096);
                
                if ($data === false || $data === '') {
                    $this->disconnect($client_id);
                    continue;
                }
                
                if (!$this->clients[$client_id]['handshake']) {
                    $this->handshake($client_id, $data);
                } else {
                    $this->processMessage($client_id, $data);
                }
            }
            
            // Heartbeat check
            if (time() - $this->lastHeartbeat > HEARTBEAT_INTERVAL) {
                $this->sendHeartbeat();
                $this->cleanupDeadConnections();
                $this->lastHeartbeat = time();
            }
            
            // Process queued notifications from database
            $this->processNotificationQueue();
        }
        
        socket_close($this->master);
    }
    
    private function handshake($client_id, $headers) {
        $lines = explode("\n", $headers);
        $key = '';
        
        foreach ($lines as $line) {
            if (preg_match('/Sec-WebSocket-Key: (.*)/', $line, $matches)) {
                $key = trim($matches[1]);
                break;
            }
        }
        
        if (empty($key)) {
            $this->disconnect($client_id);
            return;
        }
        
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        
        $response = "HTTP/1.1 101 Switching Protocols\r\n";
        $response .= "Upgrade: websocket\r\n";
        $response .= "Connection: Upgrade\r\n";
        $response .= "Sec-WebSocket-Accept: {$acceptKey}\r\n\r\n";
        
        socket_write($this->clients[$client_id]['socket'], $response, strlen($response));
        $this->clients[$client_id]['handshake'] = true;
        
        $this->log("Client {$client_id} connected");
    }
    
    private function processMessage($client_id, $data) {
        $decoded = $this->decode($data);
        
        if ($decoded === false || empty($decoded['payload'])) {
            return;
        }
        
        $message = json_decode($decoded['payload'], true);
        
        if (!$message || !isset($message['type'])) {
            return;
        }
        
        $this->clients[$client_id]['last_ping'] = time();
        
        switch ($message['type']) {
            case 'auth':
                $this->handleAuth($client_id, $message);
                break;
                
            case 'ping':
                $this->send($client_id, ['type' => 'pong']);
                break;
                
            case 'pong':
                // Heartbeat response from client
                break;
                
            case 'location_update':
                $this->handleLocationUpdate($client_id, $message);
                break;
                
            case 'status_update':
                $this->handleStatusUpdate($client_id, $message);
                break;
                
            default:
                $this->log("Unknown message type: {$message['type']}");
        }
    }
    
    private function handleAuth($client_id, $message) {
        $user_id = $message['user_id'] ?? null;
        $role = $message['role'] ?? null;
        
        if (!$user_id || !$role) {
            $this->send($client_id, ['type' => 'auth_error', 'message' => 'Invalid credentials']);
            return;
        }
        
        // Verify user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $this->send($client_id, ['type' => 'auth_error', 'message' => 'User not found']);
            return;
        }
        
        $this->clients[$client_id]['user_id'] = $user_id;
        $this->clients[$client_id]['role'] = $role;
        
        // Update connection in database
        $stmt = $this->db->prepare("INSERT INTO realtime_connections (user_id, role, connected_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE connected_at = NOW()");
        $stmt->bind_param("is", $user_id, $role);
        $stmt->execute();
        
        $this->send($client_id, ['type' => 'auth_success', 'message' => 'Authenticated']);
        $this->log("User {$user_id} ({$role}) authenticated");
    }
    
    private function handleLocationUpdate($client_id, $message) {
        $user_id = $this->clients[$client_id]['user_id'];
        
        if (!$user_id) return;
        
        $lat = $message['lat'] ?? null;
        $lng = $message['lng'] ?? null;
        
        if ($lat && $lng) {
            // Update driver location
            $stmt = $this->db->prepare("UPDATE users SET current_latitude = ?, current_longitude = ?, location_updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ddi", $lat, $lng, $user_id);
            $stmt->execute();
            
            // Broadcast to relevant riders
            $this->broadcastDriverLocation($user_id, $lat, $lng);
        }
    }
    
    private function handleStatusUpdate($client_id, $message) {
        $user_id = $this->clients[$client_id]['user_id'];
        $ride_id = $message['ride_id'] ?? null;
        $status = $message['status'] ?? null;
        
        if (!$user_id || !$ride_id || !$status) return;
        
        // Update ride status
        $stmt = $this->db->prepare("UPDATE active_rides SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $ride_id);
        $stmt->execute();
        
        // Get ride participants
        $stmt = $this->db->prepare("SELECT user_id, driver_id FROM active_rides WHERE id = ?");
        $stmt->bind_param("i", $ride_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ride = $result->fetch_assoc();
        
        if ($ride) {
            // Notify rider and driver
            $this->sendToUser($ride['user_id'], [
                'type' => 'status_update',
                'ride_id' => $ride_id,
                'status' => $status
            ]);
            
            $this->sendToUser($ride['driver_id'], [
                'type' => 'status_update',
                'ride_id' => $ride_id,
                'status' => $status
            ]);
        }
    }
    
    private function broadcastDriverLocation($driver_id, $lat, $lng) {
        // Find active rides for this driver
        $stmt = $this->db->prepare("SELECT user_id FROM active_rides WHERE driver_id = ? AND status IN ('confirmed', 'arrived', 'in_progress')");
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $this->sendToUser($row['user_id'], [
                'type' => 'driver_location',
                'driver_id' => $driver_id,
                'lat' => $lat,
                'lng' => $lng
            ]);
        }
    }
    
    private function processNotificationQueue() {
        // Get pending notifications
        $result = $this->db->query("SELECT * FROM realtime_notifications WHERE status = 'pending' ORDER BY created_at ASC LIMIT 50");
        
        while ($notification = $result->fetch_assoc()) {
            $data = json_decode($notification['data'], true);
            
            if ($notification['target_type'] === 'user') {
                $this->sendToUser($notification['target_id'], $data);
            } elseif ($notification['target_type'] === 'role') {
                $this->broadcastToRole($notification['target_id'], $data);
            }
            
            // Mark as sent
            $stmt = $this->db->prepare("UPDATE realtime_notifications SET status = 'sent', sent_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $notification['id']);
            $stmt->execute();
        }
    }
    
    private function sendToUser($user_id, $data) {
        foreach ($this->clients as $client_id => $client) {
            if ($client['user_id'] == $user_id && $client['handshake']) {
                $this->send($client_id, $data);
            }
        }
    }
    
    private function broadcastToRole($role, $data) {
        foreach ($this->clients as $client_id => $client) {
            if ($client['role'] === $role && $client['handshake']) {
                $this->send($client_id, $data);
            }
        }
    }
    
    private function send($client_id, $data) {
        if (!isset($this->clients[$client_id])) return;
        
        $payload = json_encode($data);
        $frame = $this->encode($payload);
        
        @socket_write($this->clients[$client_id]['socket'], $frame, strlen($frame));
    }
    
    private function sendHeartbeat() {
        foreach ($this->clients as $client_id => $client) {
            if ($client['handshake']) {
                $this->send($client_id, ['type' => 'ping']);
            }
        }
    }
    
    private function cleanupDeadConnections() {
        $timeout = time() - 60; // 60 second timeout
        
        foreach ($this->clients as $client_id => $client) {
            if ($client['last_ping'] < $timeout) {
                $this->disconnect($client_id);
            }
        }
    }
    
    private function disconnect($client_id) {
        if (!isset($this->clients[$client_id])) return;
        
        $user_id = $this->clients[$client_id]['user_id'];
        
        if ($user_id) {
            // Remove from database
            $stmt = $this->db->prepare("DELETE FROM realtime_connections WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        
        @socket_close($this->clients[$client_id]['socket']);
        
        $key = array_search($this->clients[$client_id]['socket'], $this->sockets);
        if ($key !== false) {
            unset($this->sockets[$key]);
        }
        
        unset($this->clients[$client_id]);
        
        $this->log("Client {$client_id} disconnected");
    }
    
    // WebSocket frame encoding (RFC 6455)
    private function encode($payload) {
        $length = strlen($payload);
        $frame = chr(129); // FIN + Text frame
        
        if ($length <= 125) {
            $frame .= chr($length);
        } elseif ($length <= 65535) {
            $frame .= chr(126) . pack('n', $length);
        } else {
            $frame .= chr(127) . pack('NN', 0, $length);
        }
        
        return $frame . $payload;
    }
    
    // WebSocket frame decoding (RFC 6455)
    private function decode($data) {
        if (strlen($data) < 2) return false;
        
        $unmaskedPayload = '';
        $decodedData = [];
        
        $firstByte = ord($data[0]);
        $secondByte = ord($data[1]);
        
        $masked = ($secondByte & 128) === 128;
        $payloadLength = $secondByte & 127;
        
        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
        } elseif ($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
        } else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
        }
        
        $dataLength = strlen($data) - $payloadOffset;
        
        for ($i = 0; $i < $dataLength; $i++) {
            $unmaskedPayload .= $data[$payloadOffset + $i] ^ $mask[$i % 4];
        }
        
        $decodedData['payload'] = $unmaskedPayload;
        
        return $decodedData;
    }
    
    private function log($message) {
        echo "[" . date('Y-m-d H:i:s') . "] {$message}\n";
    }
}

// Start server
$server = new WebSocketServer(WS_HOST, WS_PORT);
$server->run();
