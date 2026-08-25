<?php
require_once __DIR__ . '/../engine_config.php';

class MarketOverview {
    private $db;
    public function __construct() {
        $this->db = new PDO('sqlite:' . DB_PATH);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    public function create(array $data) {
        $stmt = $this->db->prepare('INSERT INTO market_overview (title, subtitle, content, featured_image, banner_image, external_url, button_text, button_url, video_url, display_priority, category, status, start_date, expiry_date, is_featured) VALUES (:title, :subtitle, :content, :featured_image, :banner_image, :external_url, :button_text, :button_url, :video_url, :display_priority, :category, :status, :start_date, :expiry_date, :is_featured)');
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    public function update(int $id, array $data) {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE market_overview SET title = :title, subtitle = :subtitle, content = :content, featured_image = :featured_image, banner_image = :banner_image, external_url = :external_url, button_text = :button_text, button_url = :button_url, video_url = :video_url, display_priority = :display_priority, category = :category, status = :status, start_date = :start_date, expiry_date = :expiry_date, is_featured = :is_featured WHERE id = :id');
        return $stmt->execute($data);
    }
    public function delete(int $id) {
        $stmt = $this->db->prepare('DELETE FROM market_overview WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
    public function getById(int $id) {
        $stmt = $this->db->prepare('SELECT * FROM market_overview WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getPublished(): array {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare('SELECT * FROM market_overview WHERE status = "published" AND (start_date IS NULL OR start_date <= :now) AND (expiry_date IS NULL OR expiry_date >= :now) ORDER BY display_priority DESC, created_at DESC');
        $stmt->execute(['now' => $now]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAll(): array {
        $stmt = $this->db->query('SELECT * FROM market_overview ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
