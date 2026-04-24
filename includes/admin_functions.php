<?php
require_once 'db.php';

class AdminFunctions {
    private $db;
    
    public function __construct() {
        $this->db = new DBHelper();
    }
    
    public function getDashboardStats() {
        return $this->db->fetchOne("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
                COUNT(DISTINCT user_id) as total_customers,
                SUM(CASE WHEN DATE(order_date) = CURDATE() THEN 1 ELSE 0 END) as today_orders,
                (SELECT COUNT(*) FROM products WHERE stock_quantity < 10) as low_stock_items,
                (SELECT COUNT(*) FROM users WHERE user_type = 'parent') as total_parents
            FROM orders
        ");
    }
    
    public function getRecentOrders($limit = 10) {
        return $this->db->fetchAll("
            SELECT o.*, u.full_name, u.email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.order_date DESC 
            LIMIT ?
        ", [$limit]);
    }
    
    public function getSalesReport($start_date, $end_date) {
        return $this->db->fetchAll("
            SELECT 
                DATE(order_date) as date,
                COUNT(*) as order_count,
                SUM(total_amount) as total_sales,
                AVG(total_amount) as average_order_value
            FROM orders 
            WHERE order_date BETWEEN ? AND ? 
            AND payment_status = 'paid'
            GROUP BY DATE(order_date)
            ORDER BY date
        ", [$start_date, $end_date]);
    }
    
    public function getProductPerformance() {
        return $this->db->fetchAll("
            SELECT 
                p.name,
                p.category_id,
                c.name as category_name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.quantity * oi.unit_price) as total_revenue,
                p.stock_quantity
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE o.payment_status = 'paid'
            GROUP BY p.id
            ORDER BY total_sold DESC
        ");
    }
    
    public function updateOrderStatus($order_id, $status) {
        $valid_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            return false;
        }
        
        return $this->db->update('orders', ['status' => $status], "id = $order_id");
    }
    
    public function getUserStats() {
        return $this->db->fetchOne("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN user_type = 'parent' THEN 1 ELSE 0 END) as parent_users,
                SUM(CASE WHEN user_type = 'admin' THEN 1 ELSE 0 END) as admin_users,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users
            FROM users
        ");
    }
    
    public function getLowStockProducts() {
        return $this->db->fetchAll("
            SELECT * FROM products 
            WHERE stock_quantity < 10 AND is_active = 1
            ORDER BY stock_quantity ASC
        ");
    }
}
?>