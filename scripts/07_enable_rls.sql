-- Enable Row Level Security (RLS) on all tables
ALTER TABLE user_profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE flight_requests ENABLE ROW LEVEL SECURITY;
ALTER TABLE flight_responses ENABLE ROW LEVEL SECURITY;
ALTER TABLE flight_response_likes ENABLE ROW LEVEL SECURITY;

-- Create RLS policies for user_profiles
CREATE POLICY "Users can view their own profile" ON user_profiles
  FOR SELECT USING (auth.uid() = id);

CREATE POLICY "Users can update their own profile" ON user_profiles
  FOR UPDATE USING (auth.uid() = id);

CREATE POLICY "Admins can view all profiles" ON user_profiles
  FOR SELECT USING (
    EXISTS (
      SELECT 1 FROM user_profiles 
      WHERE id = auth.uid() AND is_admin = TRUE
    )
  );

-- Create RLS policies for flight_requests
CREATE POLICY "Users can view their own requests" ON flight_requests
  FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can create their own requests" ON flight_requests
  FOR INSERT WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Airline staff can view requests for their airline" ON flight_requests
  FOR SELECT USING (
    EXISTS (
      SELECT 1 FROM user_profiles 
      WHERE id = auth.uid() 
      AND airline_id = flight_requests.airline_id 
      AND is_approved = TRUE
    )
  );

-- Create RLS policies for flight_responses
CREATE POLICY "Users can view responses to visible requests" ON flight_responses
  FOR SELECT USING (
    EXISTS (
      SELECT 1 FROM flight_requests fr
      JOIN user_profiles up ON up.id = auth.uid()
      WHERE fr.id = flight_responses.request_id
      AND (fr.user_id = auth.uid() OR up.airline_id = fr.airline_id)
    )
  );

CREATE POLICY "Approved airline staff can create responses" ON flight_responses
  FOR INSERT WITH CHECK (
    EXISTS (
      SELECT 1 FROM user_profiles 
      WHERE id = auth.uid() 
      AND is_approved = TRUE
    )
  );
