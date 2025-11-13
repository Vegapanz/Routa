<?php
require_once 'php/config.php';

// Simple authentication check (you can enhance this)
// For now, just checking if accessing from localhost
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    die('Access denied');
}

// Get all applications
$stmt = $pdo->query("SELECT * FROM driver_applications ORDER BY application_date DESC");
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Applications - Admin View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-pending { background: #fef3c7; color: #78350f; }
        .status-under_review { background: #dbeafe; color: #1e40af; }
        .status-approved { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Driver Applications</h1>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>License #</th>
                                <th>Vehicle</th>
                                <th>Status</th>
                                <th>Applied</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($applications)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                                        <p class="text-muted mt-2">No applications yet</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['id']) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($app['email']) ?></td>
                                        <td><?= htmlspecialchars($app['phone']) ?></td>
                                        <td><?= htmlspecialchars($app['license_number']) ?></td>
                                        <td><?= htmlspecialchars($app['vehicle_make'] . ' ' . $app['vehicle_model']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= htmlspecialchars($app['status']) ?>">
                                                <?= ucfirst(str_replace('_', ' ', htmlspecialchars($app['status']))) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($app['application_date'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewApplication(<?= $app['id'] ?>)">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-4">
            <strong><i class="bi bi-info-circle me-2"></i>Note:</strong> 
            This is a basic view for testing. A full admin dashboard with approval features should be implemented for production use.
        </div>
    </div>

    <!-- View Application Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="applicationDetails">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewApplication(id) {
            const modal = new bootstrap.Modal(document.getElementById('viewModal'));
            modal.show();
            
            // Fetch application details
            fetch(`php/get_application_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayApplicationDetails(data.application);
                    } else {
                        document.getElementById('applicationDetails').innerHTML = 
                            '<div class="alert alert-danger">Failed to load application details</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('applicationDetails').innerHTML = 
                        '<div class="alert alert-danger">Error loading application details</div>';
                });
        }

        function displayApplicationDetails(app) {
            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Personal Information</h6>
                        <p><strong>Name:</strong> ${app.first_name} ${app.middle_name || ''} ${app.last_name}</p>
                        <p><strong>Email:</strong> ${app.email}</p>
                        <p><strong>Phone:</strong> ${app.phone}</p>
                        <p><strong>Address:</strong> ${app.address}, ${app.barangay}, ${app.city} ${app.zip_code}</p>
                        <p><strong>Date of Birth:</strong> ${app.date_of_birth}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Driver Information</h6>
                        <p><strong>License #:</strong> ${app.license_number}</p>
                        <p><strong>License Expiry:</strong> ${app.license_expiry}</p>
                        <p><strong>Experience:</strong> ${app.driving_experience}</p>
                        <p><strong>Emergency Contact:</strong> ${app.emergency_name} (${app.emergency_phone})</p>
                        <p><strong>Relationship:</strong> ${app.relationship}</p>
                    </div>
                    <div class="col-md-12 mt-3">
                        <h6 class="fw-bold mb-3">Vehicle Information</h6>
                        <p><strong>Type:</strong> ${app.vehicle_type}</p>
                        <p><strong>Plate #:</strong> ${app.plate_number}</p>
                        <p><strong>Make/Model:</strong> ${app.vehicle_make} ${app.vehicle_model} (${app.vehicle_year})</p>
                        <p><strong>Franchise #:</strong> ${app.franchise_number}</p>
                    </div>
                    <div class="col-md-12 mt-3">
                        <h6 class="fw-bold mb-3">Documents</h6>
                        ${app.license_document ? `<p><a href="${app.license_document}" target="_blank" class="btn btn-sm btn-outline-primary">View License</a></p>` : ''}
                        ${app.government_id_document ? `<p><a href="${app.government_id_document}" target="_blank" class="btn btn-sm btn-outline-primary">View Gov ID</a></p>` : ''}
                        ${app.registration_document ? `<p><a href="${app.registration_document}" target="_blank" class="btn btn-sm btn-outline-primary">View Registration</a></p>` : ''}
                    </div>
                </div>
            `;
            document.getElementById('applicationDetails').innerHTML = html;
        }
    </script>
</body>
</html>
