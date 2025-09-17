-- Create flight_requests table for flight load requests
CREATE TABLE IF NOT EXISTS flight_requests (
  id SERIAL PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
  airline_id INTEGER REFERENCES airlines(id),
  flight_number VARCHAR(10) NOT NULL,
  from_airport_id INTEGER REFERENCES airports(id),
  to_airport_id INTEGER REFERENCES airports(id),
  travel_date DATE NOT NULL,
  is_return BOOLEAN DEFAULT FALSE,
  return_airline_id INTEGER REFERENCES airlines(id),
  return_flight_number VARCHAR(10),
  return_from_airport_id INTEGER REFERENCES airports(id),
  return_to_airport_id INTEGER REFERENCES airports(id),
  return_travel_date DATE,
  traveler_airline_affiliation_id INTEGER REFERENCES airlines(id),
  notes TEXT,
  status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'answered', 'expired')),
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_flight_requests_user_id ON flight_requests(user_id);
CREATE INDEX IF NOT EXISTS idx_flight_requests_airline_id ON flight_requests(airline_id);
CREATE INDEX IF NOT EXISTS idx_flight_requests_travel_date ON flight_requests(travel_date);
CREATE INDEX IF NOT EXISTS idx_flight_requests_status ON flight_requests(status);
