-- Create flight_responses table for flight load responses
CREATE TABLE IF NOT EXISTS flight_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL,
  responder_id VARCHAR(36) NOT NULL,
  response_text TEXT NOT NULL,
  likes_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (request_id) REFERENCES flight_requests(id) ON DELETE CASCADE,
  FOREIGN KEY (responder_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create flight_response_likes table for tracking likes
CREATE TABLE IF NOT EXISTS flight_response_likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  response_id INT NOT NULL,
  user_id VARCHAR(36) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (response_id) REFERENCES flight_responses(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_response_user (response_id, user_id)
);

-- Create indexes
CREATE INDEX idx_flight_responses_request_id ON flight_responses(request_id);
CREATE INDEX idx_flight_responses_responder_id ON flight_responses(responder_id);
CREATE INDEX idx_flight_response_likes_response_id ON flight_response_likes(response_id);
CREATE INDEX idx_flight_response_likes_user_id ON flight_response_likes(user_id);
