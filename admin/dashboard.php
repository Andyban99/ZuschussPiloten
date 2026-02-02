<?php
/**
 * Zuschuss Piloten - Admin Dashboard
 * 
 * Lead-Übersicht mit Tabelle
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Login erforderlich
requireLogin();

// Leads abrufen
$leads = [];
try {
    $leads = getAllLeads();
} catch (Exception $e) {
    $dbError = DEBUG_MODE ? $e->getMessage() : 'Datenbankfehler';
}

// Status-Farben
$statusColors = [
    'neu' => '#3182ce',
    'kontaktiert' => '#d69e2e',
    'qualifiziert' => '#805ad5',
    'abgeschlossen' => '#38a169',
    'abgelehnt' => '#e53e3e'
];

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard - Zuschuss Piloten Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <div class="logo">✈ Zuschuss Piloten</div>
                <span class="header-title">Admin Dashboard</span>
            </div>
            <div class="header-right">
                <span class="user-info">Angemeldet als: <strong><?= sanitize($_SESSION['admin_username'] ?? 'Admin') ?></strong></span>
                <a href="logout.php" class="btn btn-outline btn-sm">Abmelden</a>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="content-header">
                <h1>Leads / Anfragen</h1>
                <p class="lead-count"><?= count($leads) ?> Einträge</p>
            </div>
            
            <?php if (isset($dbError)): ?>
                <div class="alert alert-error">
                    Fehler beim Laden der Daten: <?= sanitize($dbError) ?>
                </div>
            <?php elseif (empty($leads)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <h2>Noch keine Anfragen</h2>
                    <p>Sobald jemand das Formular ausfüllt, erscheinen die Leads hier.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="leads-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Datum</th>
                                <th>Status</th>
                                <th>Unternehmen</th>
                                <th>Ansprechpartner</th>
                                <th>E-Mail</th>
                                <th>Branche</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td class="cell-id">#<?= $lead['id'] ?></td>
                                    <td class="cell-date">
                                        <?= date('d.m.Y', strtotime($lead['created_at'])) ?>
                                        <span class="time"><?= date('H:i', strtotime($lead['created_at'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge" style="background-color: <?= $statusColors[$lead['status']] ?? '#718096' ?>">
                                            <?= ucfirst(sanitize($lead['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="cell-company"><?= sanitize($lead['company']) ?></td>
                                    <td><?= sanitize($lead['contact_name']) ?></td>
                                    <td>
                                        <a href="mailto:<?= sanitize($lead['email']) ?>"><?= sanitize($lead['email']) ?></a>
                                    </td>
                                    <td><?= sanitize($lead['industry'] ?? '-') ?></td>
                                    <td class="cell-actions">
                                        <button class="btn btn-sm btn-outline" onclick="showDetails(<?= $lead['id'] ?>)">Details</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Lead Details Modal -->
    <div id="detailsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Lead Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Inhalt wird per JavaScript geladen -->
            </div>
        </div>
    </div>
    
    <script>
        // Lead-Daten für Modal
        const leadsData = <?= json_encode($leads, JSON_UNESCAPED_UNICODE) ?>;
        
        function showDetails(id) {
            const lead = leadsData.find(l => l.id == id);
            if (!lead) return;
            
            const modal = document.getElementById('detailsModal');
            const body = document.getElementById('modalBody');
            
            body.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-row">
                        <label>ID:</label>
                        <span>#${lead.id}</span>
                    </div>
                    <div class="detail-row">
                        <label>Erstellt am:</label>
                        <span>${new Date(lead.created_at).toLocaleString('de-DE')}</span>
                    </div>
                    <div class="detail-row">
                        <label>Status:</label>
                        <span class="status-badge" style="background-color: ${getStatusColor(lead.status)}">${lead.status}</span>
                    </div>
                    <div class="detail-row">
                        <label>Unternehmen:</label>
                        <span>${escapeHtml(lead.company)}</span>
                    </div>
                    <div class="detail-row">
                        <label>Ansprechpartner:</label>
                        <span>${escapeHtml(lead.contact_name)}</span>
                    </div>
                    <div class="detail-row">
                        <label>E-Mail:</label>
                        <span><a href="mailto:${escapeHtml(lead.email)}">${escapeHtml(lead.email)}</a></span>
                    </div>
                    <div class="detail-row">
                        <label>Telefon:</label>
                        <span>${lead.phone ? `<a href="tel:${escapeHtml(lead.phone)}">${escapeHtml(lead.phone)}</a>` : '-'}</span>
                    </div>
                    <div class="detail-row">
                        <label>Adresse:</label>
                        <span>${escapeHtml(lead.address || '-')}</span>
                    </div>
                    <div class="detail-row">
                        <label>Branche:</label>
                        <span>${escapeHtml(lead.industry || '-')}</span>
                    </div>
                    <div class="detail-row">
                        <label>Mitarbeiter:</label>
                        <span>${escapeHtml(lead.employees || '-')}</span>
                    </div>
                    <div class="detail-row full-width">
                        <label>Projektbeschreibung:</label>
                        <p class="project-desc">${escapeHtml(lead.project_description || '-')}</p>
                    </div>
                </div>
            `;
            
            modal.style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }
        
        function getStatusColor(status) {
            const colors = {
                'neu': '#3182ce',
                'kontaktiert': '#d69e2e',
                'qualifiziert': '#805ad5',
                'abgeschlossen': '#38a169',
                'abgelehnt': '#e53e3e'
            };
            return colors[status] || '#718096';
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Modal schließen bei Klick außerhalb
        document.getElementById('detailsModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        
        // ESC-Taste schließt Modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>
