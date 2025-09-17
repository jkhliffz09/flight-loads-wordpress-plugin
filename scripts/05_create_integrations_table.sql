-- Create integrations table for wishlist member and other integrations
CREATE TABLE IF NOT EXISTS integrations (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type VARCHAR(50) NOT NULL,
  api_url TEXT,
  api_key TEXT,
  is_connected BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Insert default wishlist member integration
INSERT INTO integrations (name, type, is_connected) VALUES
('Wishlist Member', 'membership', FALSE)
ON CONFLICT DO NOTHING;
