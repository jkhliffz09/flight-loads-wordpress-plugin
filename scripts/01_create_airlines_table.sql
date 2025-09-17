-- Create airlines table for airline domain management
CREATE TABLE IF NOT EXISTS airlines (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  iata_code VARCHAR(3) NOT NULL UNIQUE,
  domain VARCHAR(255) NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Insert some sample airlines
INSERT INTO airlines (name, iata_code, domain) VALUES
('American Airlines', 'AA', 'aa.com'),
('Delta Air Lines', 'DL', 'delta.com'),
('United Airlines', 'UA', 'united.com'),
('Southwest Airlines', 'WN', 'southwest.com'),
('JetBlue Airways', 'B6', 'jetblue.com'),
('Alaska Airlines', 'AS', 'alaskaair.com'),
('Spirit Airlines', 'NK', 'spirit.com'),
('Frontier Airlines', 'F9', 'flyfrontier.com')
ON CONFLICT (iata_code) DO NOTHING;
