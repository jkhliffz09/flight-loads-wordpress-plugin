-- Create flight_responses table for flight load responses
CREATE TABLE IF NOT EXISTS flight_responses (
  id SERIAL PRIMARY KEY,
  request_id INTEGER REFERENCES flight_requests(id) ON DELETE CASCADE,
  responder_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
  response_text TEXT NOT NULL,
  likes_count INTEGER DEFAULT 0,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create flight_response_likes table for tracking likes
CREATE TABLE IF NOT EXISTS flight_response_likes (
  id SERIAL PRIMARY KEY,
  response_id INTEGER REFERENCES flight_responses(id) ON DELETE CASCADE,
  user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  UNIQUE(response_id, user_id)
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_flight_responses_request_id ON flight_responses(request_id);
CREATE INDEX IF NOT EXISTS idx_flight_responses_responder_id ON flight_responses(responder_id);
CREATE INDEX IF NOT EXISTS idx_flight_response_likes_response_id ON flight_response_likes(response_id);
CREATE INDEX IF NOT EXISTS idx_flight_response_likes_user_id ON flight_response_likes(user_id);
