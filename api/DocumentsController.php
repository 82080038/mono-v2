<?php
/**
 * Document Management Controller
 * Handles document upload, templates, and management
 */

class DocumentsController {
    private $db;
    private $uploadDir;
    
    public function __construct($database) {
        $this->db = $database;
        $this->uploadDir = __DIR__ . '/../uploads/documents/';
        
        // Create upload directory if not exists
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Get all documents with filtering
     */
    public function getDocuments($filters = []) {
        $query = "SELECT d.*, m.name as member_name, u.name as uploaded_by_name 
                 FROM documents d 
                 LEFT JOIN members m ON d.member_id = m.id 
                 LEFT JOIN users u ON d.uploaded_by = u.id 
                 WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['type'])) {
            $query .= " AND d.type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['member_id'])) {
            $query .= " AND d.member_id = ?";
            $params[] = $filters['member_id'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (d.filename LIKE ? OR d.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND d.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND d.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $query .= " ORDER BY d.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $query .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting documents: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Upload document
     */
    public function uploadDocument($file, $data) {
        try {
            // Validate file
            if (!$this->validateFile($file)) {
                return ['success' => false, 'message' => 'Invalid file format or size'];
            }
            
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file['name']);
            $filepath = $this->uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return ['success' => false, 'message' => 'Failed to upload file'];
            }
            
            // Save document record
            $query = "INSERT INTO documents (filename, original_name, type, size, member_id, description, uploaded_by, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                $filename,
                $file['name'],
                $data['type'],
                $file['size'],
                $data['member_id'] ?? null,
                $data['description'] ?? '',
                $data['uploaded_by']
            ]);
            
            if ($result) {
                $documentId = $this->db->lastInsertId();
                return [
                    'success' => true,
                    'message' => 'Document uploaded successfully',
                    'document_id' => $documentId,
                    'filename' => $filename
                ];
            } else {
                // Remove uploaded file if database insert failed
                unlink($filepath);
                return ['success' => false, 'message' => 'Failed to save document record'];
            }
            
        } catch (Exception $e) {
            error_log("Error uploading document: " . $e->getMessage());
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get document by ID
     */
    public function getDocument($id) {
        $query = "SELECT d.*, m.name as member_name, u.name as uploaded_by_name 
                 FROM documents d 
                 LEFT JOIN members m ON d.member_id = m.id 
                 LEFT JOIN users u ON d.uploaded_by = u.id 
                 WHERE d.id = ?";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting document: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Delete document
     */
    public function deleteDocument($id) {
        try {
            // Get document info
            $document = $this->getDocument($id);
            if (!$document) {
                return ['success' => false, 'message' => 'Document not found'];
            }
            
            // Delete file
            $filepath = $this->uploadDir . $document['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            // Delete database record
            $query = "DELETE FROM documents WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Document deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete document'];
            }
            
        } catch (Exception $e) {
            error_log("Error deleting document: " . $e->getMessage());
            return ['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get document templates
     */
    public function getTemplates() {
        $query = "SELECT * FROM document_templates ORDER BY name";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting templates: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create document template
     */
    public function createTemplate($data) {
        try {
            $query = "INSERT INTO document_templates (name, type, content, variables, created_by, created_at) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                $data['name'],
                $data['type'],
                $data['content'],
                $data['variables'],
                $data['created_by']
            ]);
            
            if ($result) {
                $templateId = $this->db->lastInsertId();
                return [
                    'success' => true,
                    'message' => 'Template created successfully',
                    'template_id' => $templateId
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create template'];
            }
            
        } catch (Exception $e) {
            error_log("Error creating template: " . $e->getMessage());
            return ['success' => false, 'message' => 'Template creation failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Generate document from template
     */
    public function generateFromTemplate($templateId, $data) {
        try {
            // Get template
            $template = $this->getTemplate($templateId);
            if (!$template) {
                return ['success' => false, 'message' => 'Template not found'];
            }
            
            // Replace variables
            $content = $template['content'];
            $variables = explode(',', $template['variables']);
            
            foreach ($variables as $variable) {
                $variable = trim($variable);
                $placeholder = '{' . $variable . '}';
                $value = $data[$variable] ?? '';
                $content = str_replace($placeholder, $value, $content);
            }
            
            // Create temporary file
            $filename = 'generated_' . date('Y-m-d_H-i-s') . '.txt';
            $filepath = $this->uploadDir . $filename;
            
            file_put_contents($filepath, $content);
            
            // Save document record
            $query = "INSERT INTO documents (filename, original_name, type, size, member_id, description, uploaded_by, template_id, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                $filename,
                $template['name'] . ' - Generated',
                'generated',
                strlen($content),
                $data['member_id'] ?? null,
                'Generated from template: ' . $template['name'],
                $data['uploaded_by'],
                $templateId
            ]);
            
            if ($result) {
                $documentId = $this->db->lastInsertId();
                return [
                    'success' => true,
                    'message' => 'Document generated successfully',
                    'document_id' => $documentId,
                    'filename' => $filename
                ];
            } else {
                unlink($filepath);
                return ['success' => false, 'message' => 'Failed to save generated document'];
            }
            
        } catch (Exception $e) {
            error_log("Error generating document: " . $e->getMessage());
            return ['success' => false, 'message' => 'Document generation failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get document statistics
     */
    public function getDocumentStats() {
        $stats = [];
        
        try {
            // Total documents
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM documents");
            $stats['total_documents'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Documents by type
            $stmt = $this->db->query("SELECT type, COUNT(*) as count FROM documents GROUP BY type");
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Storage used
            $stmt = $this->db->query("SELECT SUM(size) as total_size FROM documents");
            $totalSize = $stmt->fetch(PDO::FETCH_ASSOC)['total_size'];
            $stats['storage_used'] = $this->formatBytes($totalSize);
            
            // Recent uploads
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM documents WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['recent_uploads'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Error getting document stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get template by ID
     */
    private function getTemplate($id) {
        $query = "SELECT * FROM document_templates WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting template: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        // Check file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            return false;
        }
        
        // Check file type
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'text/plain'
        ];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $timestamp = date('Y-m-d_H-i-s');
        $random = mt_rand(1000, 9999);
        
        return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Search documents
     */
    public function searchDocuments($searchTerm, $filters = []) {
        $query = "SELECT d.*, m.name as member_name, u.name as uploaded_by_name 
                 FROM documents d 
                 LEFT JOIN members m ON d.member_id = m.id 
                 LEFT JOIN users u ON d.uploaded_by = u.id 
                 WHERE (d.filename LIKE ? OR d.description LIKE ? OR m.name LIKE ?)";
        
        $params = ['%' . $searchTerm . '%', '%' . $searchTerm . '%', '%' . $searchTerm . '%'];
        
        // Apply additional filters
        if (!empty($filters['type'])) {
            $query .= " AND d.type = ?";
            $params[] = $filters['type'];
        }
        
        $query .= " ORDER BY d.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching documents: " . $e->getMessage());
            return [];
        }
    }
}
?>
