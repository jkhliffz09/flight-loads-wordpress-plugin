-- Create integrations table for wishlist member and other integrations
CREATE TABLE IF NOT EXISTS integrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type VARCHAR(50) NOT NULL,
  api_url TEXT,
  api_key TEXT,
  is_connected BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default wishlist member integration
INSERT IGNORE INTO integrations (name, type, is_connected) VALUES
('Wishlist Member', 'membership', FALSE);
