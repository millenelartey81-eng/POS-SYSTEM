-- =====================================================
-- POS MANAGEMENT SYSTEM - DATABASE SCHEMA
-- =====================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS pos_system;
USE pos_system;

-- =====================================================
-- 1. USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'MANAGER', 'CASHIER', 'STAFF') DEFAULT 'STAFF',
    status ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED') DEFAULT 'ACTIVE',
    profile_image VARCHAR(255),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. CATEGORIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    slug VARCHAR(100) UNIQUE NOT NULL,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. PRODUCTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    sku VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    cost_price DECIMAL(10, 2),
    quantity_in_stock INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    reorder_quantity INT DEFAULT 50,
    image VARCHAR(255),
    barcode VARCHAR(100) UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    tax_rate DECIMAL(5, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_sku (sku),
    INDEX idx_barcode (barcode),
    INDEX idx_category_id (category_id),
    INDEX idx_is_active (is_active),
    FULLTEXT INDEX ft_name_description (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. CUSTOMERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    customer_code VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    customer_type ENUM('WALK_IN', 'REGULAR', 'VIP') DEFAULT 'WALK_IN',
    loyalty_points INT DEFAULT 0,
    total_purchases DECIMAL(12, 2) DEFAULT 0,
    total_spent DECIMAL(12, 2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    last_purchase_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_phone (phone),
    INDEX idx_email (email),
    INDEX idx_customer_code (customer_code),
    INDEX idx_customer_type (customer_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. SALES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    receipt_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    cashier_id INT NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(12, 2) NOT NULL,
    tax_amount DECIMAL(12, 2) DEFAULT 0,
    discount_amount DECIMAL(12, 2) DEFAULT 0,
    discount_type ENUM('FIXED', 'PERCENTAGE') DEFAULT 'FIXED',
    total_amount DECIMAL(12, 2) NOT NULL,
    payment_method ENUM('CASH', 'CARD', 'MOBILE', 'CHEQUE', 'BANK_TRANSFER') DEFAULT 'CASH',
    amount_received DECIMAL(12, 2),
    change_given DECIMAL(12, 2) DEFAULT 0,
    notes TEXT,
    status ENUM('COMPLETED', 'PENDING', 'CANCELLED', 'REFUNDED') DEFAULT 'COMPLETED',
    refund_reason VARCHAR(255),
    refunded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (cashier_id) REFERENCES users(id),
    INDEX idx_receipt_number (receipt_number),
    INDEX idx_sale_date (sale_date),
    INDEX idx_customer_id (customer_id),
    INDEX idx_cashier_id (cashier_id),
    INDEX idx_status (status),
    INDEX idx_payment_method (payment_method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. SALE_ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS sale_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    tax_rate DECIMAL(5, 2) DEFAULT 0,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    line_total DECIMAL(12, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_sale_id (sale_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. INVENTORY_MOVEMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS inventory_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    product_id INT NOT NULL,
    movement_type ENUM('IN', 'OUT', 'ADJUSTMENT', 'RETURN', 'DAMAGE') DEFAULT 'OUT',
    quantity INT NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_product_id (product_id),
    INDEX idx_movement_type (movement_type),
    INDEX idx_created_at (created_at),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. STOCK_ADJUSTMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS stock_adjustments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    adjustment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reason VARCHAR(255) NOT NULL,
    notes TEXT,
    created_by INT NOT NULL,
    approved_by INT,
    status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_adjustment_date (adjustment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. STOCK_ADJUSTMENT_ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS stock_adjustment_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    stock_adjustment_id INT NOT NULL,
    product_id INT NOT NULL,
    old_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    quantity_difference INT NOT NULL,
    FOREIGN KEY (stock_adjustment_id) REFERENCES stock_adjustments(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_adjustment_id (stock_adjustment_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. PURCHASE_ORDERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    supplier_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expected_delivery_date DATE,
    actual_delivery_date DATE,
    subtotal DECIMAL(12, 2) NOT NULL,
    tax_amount DECIMAL(12, 2) DEFAULT 0,
    total_amount DECIMAL(12, 2) NOT NULL,
    status ENUM('PENDING', 'ORDERED', 'RECEIVED', 'CANCELLED') DEFAULT 'PENDING',
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_po_number (po_number),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. PURCHASE_ORDER_ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    purchase_order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity_ordered INT NOT NULL,
    quantity_received INT DEFAULT 0,
    unit_price DECIMAL(10, 2) NOT NULL,
    tax_rate DECIMAL(5, 2) DEFAULT 0,
    line_total DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_po_id (purchase_order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. SUPPLIERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    supplier_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(120),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    payment_terms VARCHAR(100),
    tax_id VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_supplier_code (supplier_code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. DISCOUNTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS discounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    discount_type ENUM('FIXED', 'PERCENTAGE') NOT NULL,
    value DECIMAL(10, 2) NOT NULL,
    min_purchase_amount DECIMAL(12, 2),
    max_discount_amount DECIMAL(12, 2),
    applies_to ENUM('ALL_PRODUCTS', 'SPECIFIC_PRODUCTS', 'SPECIFIC_CATEGORIES') DEFAULT 'ALL_PRODUCTS',
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    usage_limit INT,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. DISCOUNT_PRODUCTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS discount_products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    discount_id INT NOT NULL,
    product_id INT NOT NULL,
    PRIMARY KEY (discount_id, product_id),
    FOREIGN KEY (discount_id) REFERENCES discounts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 15. DISCOUNT_CATEGORIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS discount_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    discount_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (discount_id, category_id),
    FOREIGN KEY (discount_id) REFERENCES discounts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 16. PAYMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    sale_id INT NOT NULL,
    payment_method ENUM('CASH', 'CARD', 'MOBILE', 'CHEQUE', 'BANK_TRANSFER') NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    transaction_reference VARCHAR(100),
    status ENUM('PENDING', 'COMPLETED', 'FAILED', 'CANCELLED') DEFAULT 'COMPLETED',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    INDEX idx_sale_id (sale_id),
    INDEX idx_payment_method (payment_method),
    INDEX idx_status (status),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 17. RECEIPTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS receipts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    sale_id INT UNIQUE NOT NULL,
    receipt_html LONGTEXT,
    receipt_pdf VARCHAR(255),
    printed_at TIMESTAMP NULL,
    emailed_at TIMESTAMP NULL,
    email_recipient VARCHAR(120),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    INDEX idx_sale_id (sale_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 18. AUDIT_LOGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT,
    changes JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity_type (entity_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 19. CURRENCY_SETTINGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS currency_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(3) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    symbol VARCHAR(10),
    exchange_rate DECIMAL(10, 6) DEFAULT 1,
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_is_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 20. SYSTEM_SETTINGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_type ENUM('STRING', 'BOOLEAN', 'INTEGER', 'JSON') DEFAULT 'STRING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 21. NOTIFICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 22. ACTIVITY_LOGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    user_id INT,
    activity_type VARCHAR(100) NOT NULL,
    description TEXT,
    resource_type VARCHAR(100),
    resource_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

-- Additional composite indexes
ALTER TABLE sales ADD INDEX idx_cashier_date (cashier_id, sale_date);
ALTER TABLE sales ADD INDEX idx_customer_date (customer_id, sale_date);
ALTER TABLE inventory_movements ADD INDEX idx_product_date (product_id, created_at);
ALTER TABLE products ADD INDEX idx_category_stock (category_id, quantity_in_stock);

-- =====================================================
-- CREATE VIEWS FOR COMMON QUERIES
-- =====================================================

-- Sales Summary View
CREATE OR REPLACE VIEW v_sales_summary AS
SELECT 
    DATE(s.sale_date) AS sale_date,
    COUNT(s.id) AS total_transactions,
    COUNT(DISTINCT s.customer_id) AS unique_customers,
    SUM(s.total_amount) AS total_sales,
    SUM(s.tax_amount) AS total_tax,
    SUM(s.discount_amount) AS total_discounts,
    AVG(s.total_amount) AS avg_transaction_value
FROM sales s
WHERE s.status = 'COMPLETED'
GROUP BY DATE(s.sale_date);

-- Low Stock View
CREATE OR REPLACE VIEW v_low_stock_products AS
SELECT 
    p.id,
    p.uuid,
    p.sku,
    p.name,
    c.name AS category,
    p.quantity_in_stock,
    p.reorder_level,
    (p.reorder_level - p.quantity_in_stock) AS quantity_needed
FROM products p
JOIN categories c ON p.category_id = c.id
WHERE p.quantity_in_stock <= p.reorder_level
AND p.is_active = TRUE;

-- Customer Purchase History View
CREATE OR REPLACE VIEW v_customer_purchase_history AS
SELECT 
    c.id,
    c.uuid,
    c.customer_code,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    COUNT(s.id) AS total_purchases,
    SUM(s.total_amount) AS total_spent,
    MAX(s.sale_date) AS last_purchase_date,
    AVG(s.total_amount) AS avg_purchase_value
FROM customers c
LEFT JOIN sales s ON c.id = s.customer_id AND s.status = 'COMPLETED'
GROUP BY c.id;

-- Product Sales Performance View
CREATE OR REPLACE VIEW v_product_sales_performance AS
SELECT 
    p.id,
    p.uuid,
    p.sku,
    p.name,
    c.name AS category,
    COUNT(si.id) AS times_sold,
    SUM(si.quantity) AS total_quantity_sold,
    SUM(si.line_total) AS total_revenue,
    AVG(si.unit_price) AS avg_unit_price
FROM products p
JOIN categories c ON p.category_id = c.id
LEFT JOIN sale_items si ON p.id = si.product_id
LEFT JOIN sales s ON si.sale_id = s.id AND s.status = 'COMPLETED'
GROUP BY p.id;

-- Daily Sales by Payment Method View
CREATE OR REPLACE VIEW v_daily_sales_by_payment_method AS
SELECT 
    DATE(s.sale_date) AS sale_date,
    s.payment_method,
    COUNT(*) AS transaction_count,
    SUM(s.total_amount) AS total_amount
FROM sales s
WHERE s.status = 'COMPLETED'
GROUP BY DATE(s.sale_date), s.payment_method;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Update product quantity after sale
DELIMITER $$

CREATE TRIGGER trg_update_stock_after_sale
AFTER INSERT ON sale_items
FOR EACH ROW
BEGIN
    UPDATE products 
    SET quantity_in_stock = quantity_in_stock - NEW.quantity
    WHERE id = NEW.product_id;
    
    INSERT INTO inventory_movements 
    (product_id, movement_type, quantity, reference_type, reference_id, created_by)
    VALUES 
    (NEW.product_id, 'OUT', NEW.quantity, 'SALE', NEW.sale_id, 
     (SELECT cashier_id FROM sales WHERE id = NEW.sale_id));
END$$

-- Update customer purchase info after sale
CREATE TRIGGER trg_update_customer_after_sale
AFTER INSERT ON sales
FOR EACH ROW
BEGIN
    IF NEW.customer_id IS NOT NULL THEN
        UPDATE customers
        SET 
            total_purchases = total_purchases + 1,
            total_spent = total_spent + NEW.total_amount,
            last_purchase_date = NOW()
        WHERE id = NEW.customer_id;
    END IF;
END$$

-- Revert stock on sale cancellation
CREATE TRIGGER trg_revert_stock_on_sale_cancellation
AFTER UPDATE ON sales
FOR EACH ROW
BEGIN
    IF NEW.status = 'CANCELLED' AND OLD.status != 'CANCELLED' THEN
        UPDATE products 
        SET quantity_in_stock = quantity_in_stock + si.quantity
        FROM sale_items si
        WHERE si.sale_id = NEW.id AND products.id = si.product_id;
        
        DELETE FROM inventory_movements 
        WHERE reference_type = 'SALE' AND reference_id = NEW.id;
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- INSERT DEFAULT CURRENCY
-- =====================================================
INSERT INTO currency_settings (code, name, symbol, is_default, is_active) 
VALUES ('USD', 'United States Dollar', '$', TRUE, TRUE);

-- =====================================================
-- INSERT DEFAULT SETTINGS
-- =====================================================
INSERT INTO system_settings (setting_key, setting_value, setting_type) VALUES
('app_name', 'POS Management System', 'STRING'),
('app_currency', 'USD', 'STRING'),
('company_name', 'Your Company Name', 'STRING'),
('company_email', 'info@company.com', 'STRING'),
('company_phone', '+1234567890', 'STRING'),
('receipt_footer_text', 'Thank you for your purchase!', 'STRING'),
('default_tax_rate', '0', 'STRING'),
('low_stock_threshold', '10', 'STRING'),
('receipt_print_copies', '1', 'STRING'),
('enable_barcode_scanning', 'true', 'BOOLEAN'),
('enable_email_receipts', 'true', 'BOOLEAN'),
('enable_loyalty_program', 'true', 'BOOLEAN');
