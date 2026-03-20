<?php
/**
 * Guarantee & Risk Management System
 * Handle loan guarantees and collective risk assessment
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_GET["action"] ?? "dashboard";
    
    switch ($action) {
        case "dashboard":
            handleGuaranteeDashboard($pdo);
            break;
        case "get_guarantees":
            handleGetGuarantees($pdo);
            break;
        case "create_guarantee":
            handleCreateGuarantee($pdo);
            break;
        case "get_guarantee_details":
            handleGetGuaranteeDetails($pdo);
            break;
        case "collective_risk_assessment":
            handleCollectiveRiskAssessment($pdo);
            break;
        case "guarantee_relationship_analysis":
            handleGuaranteeRelationshipAnalysis($pdo);
            break;
        case "guarantee_payment_tracking":
            handleGuaranteePaymentTracking($pdo);
            break;
        case "guarantee_notification":
            handleGuaranteeNotification($pdo);
            break;
        case "guarantee_report":
            handleGuaranteeReport($pdo);
            break;
        case "guarantee_collection_strategy":
            handleGuaranteeCollectionStrategy($pdo);
            break;
        case "guarantee_legal_action":
            handleGuaranteeLegalAction($pdo);
            break;
        default:
            throw new Exception("Invalid action");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}

function handleGuaranteeDashboard($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as total_guarantees FROM loan_guarantees WHERE status = 'Active'");
    $totalGuarantees = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as total_risk_assessments FROM guarantee_risk_assessments WHERE status = 'Completed'");
    $totalRiskAssessments = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as total_relationships FROM guarantee_relationships WHERE status = 'Active'");
    $totalRelationships = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as pending_guarantees FROM loan_guarantees WHERE status = 'Pending'");
    $pendingGuarantees = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as high_risk_count FROM guarantee_risk_assessments WHERE risk_level = 'high' AND status = 'Active'");
    $highRiskCount = $stmt->fetchColumn();

    echo json_encode([
        "success" => true,
        "data" => [
            "total_guarantees" => $totalGuarantees,
            "total_risk_assessments" => $totalRiskAssessments,
            "total_relationships" => $totalRelationships,
            "pending_guarantees" => $pendingGuarantees,
            "high_risk_count" => $highRiskCount,
        ]
    ]);
}

function handleGetGuarantees($pdo) {
    $stmt = $pdo->query("SELECT lg.*, m1.name as borrower_name, m2.name as guarantor_name 
                         FROM loan_guarantees lg 
                         LEFT JOIN members m1 ON lg.borrower_id = m1.id 
                         LEFT JOIN members m2 ON lg.guarantor_id = m2.id 
                         ORDER BY lg.created_at DESC");
    
    $guarantees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "data" => $guarantees
    ]);
}

function handleCreateGuarantee($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("INSERT INTO loan_guarantees (guarantee_id, borrower_id, guarantor_id, loan_amount, guarantee_type, description, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['guarantee_id'],
        $data['borrower_id'],
        $data['guarantor_id'],
        $data['loan_amount'],
        $data['guarantee_type'],
        $data['description'],
        $data['status'] ?? 'Pending'
    ]);
    
    echo json_encode([
        "success" => true,
        "message" => "Guarantee created successfully"
    ]);
}

function handleGetGuaranteeDetails($pdo) {
    $guaranteeId = $_GET['guarantee_id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT lg.*, m1.name as borrower_name, m2.name as guarantor_name,
                           gra.risk_level, gra.risk_score, gra.assessment_details
                           FROM loan_guarantees lg 
                           LEFT JOIN members m1 ON lg.borrower_id = m1.id 
                           LEFT JOIN members m2 ON lg.guarantor_id = m2.id
                           LEFT JOIN guarantee_risk_assessments gra ON lg.id = gra.guarantee_id
                           WHERE lg.id = ?");
    
    $stmt->execute([$guaranteeId]);
    $guarantee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($guarantee) {
        echo json_encode([
            "success" => true,
            "data" => $guarantee
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Guarantee not found"
        ]);
    }
}

function handleCollectiveRiskAssessment($pdo) {
    $stmt = $pdo->query("SELECT risk_level, COUNT(*) as count 
                         FROM guarantee_risk_assessments 
                         WHERE status = 'Active' 
                         GROUP BY risk_level");
    
    $riskData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "data" => $riskData
    ]);
}

function handleGuaranteeRelationshipAnalysis($pdo) {
    $stmt = $pdo->query("SELECT gr.relationship_type, gr.relationship_strength, COUNT(*) as count
                         FROM guarantee_relationships gr
                         WHERE gr.status = 'Active'
                         GROUP BY gr.relationship_type, gr.relationship_strength");
    
    $relationshipData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "data" => $relationshipData
    ]);
}

function handleGuaranteePaymentTracking($pdo) {
    $guaranteeId = $_GET['guarantee_id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT * FROM guarantee_payment_tracking 
                           WHERE guarantee_id = ? 
                           ORDER BY payment_date DESC");
    
    $stmt->execute([$guaranteeId]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "data" => $payments
    ]);
}

function handleGuaranteeNotification($pdo) {
    // Mock notification system
    echo json_encode([
        "success" => true,
        "data" => [
            "notifications_sent" => 5,
            "message" => "Notifications sent successfully"
        ]
    ]);
}

function handleGuaranteeReport($pdo) {
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total_guarantees,
        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_guarantees,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_guarantees,
        AVG(gra.risk_score) as avg_risk_score
        FROM loan_guarantees lg
        LEFT JOIN guarantee_risk_assessments gra ON lg.id = gra.guarantee_id");
    
    $reportData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "data" => $reportData
    ]);
}

function handleGuaranteeCollectionStrategy($pdo) {
    // Mock collection strategy
    echo json_encode([
        "success" => true,
        "data" => [
            "strategy" => "Standard Collection Process",
            "steps" => [
                "Send reminder notification",
                "Phone call follow-up",
                "Site visit",
                "Legal action preparation"
            ]
        ]
    ]);
}

function handleGuaranteeLegalAction($pdo) {
    // Mock legal action
    echo json_encode([
        "success" => true,
        "data" => [
            "legal_actions" => 2,
            "pending_cases" => 1,
            "resolved_cases" => 1
        ]
    ]);
}
?>
