-- Create flight_requests table for flight load requests
CREATE TABLE IF NOT EXISTS flight_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(36) NOT NULL,
  airline_id INT,
  flight_number VARCHAR(10) NOT NULL,
  from_airport_id INT,
  to_airport_id INT,
  travel_date DATE NOT NULL,
  is_return BOOLEAN DEFAULT FALSE,
  return_airline_id INT,
  return_flight_number VARCHAR(10),
  return_from_airport_id INT,
  return_to_airport_id INT,
  return_travel_date DATE,
  traveler_airline_affiliation_id INT,
  notes TEXT,
  status ENUM('pending', 'answered', 'expired') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (airline_id) REFERENCES airlines(id),
  FOREIGN KEY (from_airport_id) REFERENCES airports(id),
  FOREIGN KEY (to_airport_id) REFERENCES airports(id),
  FOREIGN KEY (return_airline_id) REFERENCES airlines(id),
  FOREIGN KEY (return_from_airport_id) REFERENCES airports(id),
  FOREIGN KEY (return_to_airport_id) REFERENCES airports(id),
  FOREIGN KEY (traveler_airline_affiliation_id) REFERENCES airlines(id)
);

-- Create indexes for better performance
CREATE INDEX idx_flight_requests_user_id ON flight_requests(user_id);
CREATE INDEX idx_flight_requests_airline_id ON flight_requests(airline_id);
CREATE INDEX idx_flight_requests_travel_date ON flight_requests(travel_date);
CREATE INDEX idx_flight_requests_status ON flight_requests(status);
