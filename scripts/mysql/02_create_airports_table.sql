-- Create airports table for airport data
CREATE TABLE IF NOT EXISTS airports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(4) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  city VARCHAR(255) NOT NULL,
  country VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some sample airports
INSERT IGNORE INTO airports (code, name, city, country) VALUES
('JFK', 'John F. Kennedy International Airport', 'New York', 'United States'),
('LAX', 'Los Angeles International Airport', 'Los Angeles', 'United States'),
('ORD', 'O\'Hare International Airport', 'Chicago', 'United States'),
('ATL', 'Hartsfield-Jackson Atlanta International Airport', 'Atlanta', 'United States'),
('DFW', 'Dallas/Fort Worth International Airport', 'Dallas', 'United States'),
('DEN', 'Denver International Airport', 'Denver', 'United States'),
('LAS', 'McCarran International Airport', 'Las Vegas', 'United States'),
('PHX', 'Phoenix Sky Harbor International Airport', 'Phoenix', 'United States'),
('MIA', 'Miami International Airport', 'Miami', 'United States'),
('SEA', 'Seattle-Tacoma International Airport', 'Seattle', 'United States'),
('LIS', 'Lisbon Airport', 'Lisbon', 'Portugal'),
('LHR', 'London Heathrow Airport', 'London', 'United Kingdom'),
('CDG', 'Charles de Gaulle Airport', 'Paris', 'France'),
('FRA', 'Frankfurt Airport', 'Frankfurt', 'Germany'),
('AMS', 'Amsterdam Airport Schiphol', 'Amsterdam', 'Netherlands');
