-- Create users table to extend Supabase auth with additional profile data
CREATE TABLE IF NOT EXISTS user_profiles (
  id UUID REFERENCES auth.users(id) ON DELETE CASCADE PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  username VARCHAR(50) UNIQUE NOT NULL,
  airline_id INTEGER REFERENCES airlines(id),
  status VARCHAR(20) NOT NULL CHECK (status IN ('active', 'retired')),
  phone_number VARCHAR(20),
  retirement_date DATE,
  ex_airline_job VARCHAR(255),
  years_worked INTEGER,
  retired_id_url TEXT,
  is_approved BOOLEAN DEFAULT FALSE,
  is_admin BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_user_profiles_airline_id ON user_profiles(airline_id);
CREATE INDEX IF NOT EXISTS idx_user_profiles_status ON user_profiles(status);
CREATE INDEX IF NOT EXISTS idx_user_profiles_approved ON user_profiles(is_approved);
