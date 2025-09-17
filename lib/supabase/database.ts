import { createClient } from "./server"

// Database utility functions for the flight loads system
export async function getAirlines() {
  const supabase = createClient()
  const { data, error } = await supabase.from("airlines").select("*").order("name")

  if (error) throw error
  return data
}

export async function getAirports() {
  const supabase = createClient()
  const { data, error } = await supabase.from("airports").select("*").order("name")

  if (error) throw error
  return data
}

export async function searchAirlines(searchTerm: string) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from("airlines")
    .select("*")
    .or(`name.ilike.%${searchTerm}%,iata_code.ilike.%${searchTerm}%,domain.ilike.%${searchTerm}%`)
    .order("name")

  if (error) throw error
  return data
}

export async function searchAirports(searchTerm: string) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from("airports")
    .select("*")
    .or(`name.ilike.%${searchTerm}%,code.ilike.%${searchTerm}%,city.ilike.%${searchTerm}%`)
    .order("name")
    .limit(10)

  if (error) throw error
  return data
}

export async function getUserProfile(userId: string) {
  const supabase = createClient()
  const { data, error } = await supabase
    .from("user_profiles")
    .select(`
      *,
      airline:airlines(*)
    `)
    .eq("id", userId)
    .single()

  if (error) throw error
  return data
}

export async function getUsersCount() {
  const supabase = createClient()
  const { count, error } = await supabase.from("user_profiles").select("*", { count: "exact", head: true })

  if (error) throw error
  return count || 0
}

export async function getFlightRequests(userId?: string, airlineId?: number) {
  const supabase = createClient()
  let query = supabase
    .from("flight_requests")
    .select(`
      *,
      user_profile:user_profiles(*),
      airline:airlines(*),
      from_airport:airports!flight_requests_from_airport_id_fkey(*),
      to_airport:airports!flight_requests_to_airport_id_fkey(*),
      return_from_airport:airports!flight_requests_return_from_airport_id_fkey(*),
      return_to_airport:airports!flight_requests_return_to_airport_id_fkey(*),
      traveler_airline:airlines!flight_requests_traveler_airline_affiliation_id_fkey(*)
    `)
    .order("created_at", { ascending: false })

  if (userId) {
    query = query.eq("user_id", userId)
  }

  if (airlineId) {
    query = query.eq("airline_id", airlineId)
  }

  const { data, error } = await query

  if (error) throw error
  return data
}
