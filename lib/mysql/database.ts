import { executeQuery, queryOne, queryMany } from "./connection"

// Airlines
export async function getAirlines() {
  return queryMany("SELECT * FROM airlines ORDER BY name")
}

export async function getAirlineById(id: number) {
  return queryOne("SELECT * FROM airlines WHERE id = ?", [id])
}

export async function searchAirlines(searchTerm: string) {
  return queryMany("SELECT * FROM airlines WHERE name LIKE ? OR iata_code LIKE ? OR domain LIKE ? ORDER BY name", [
    `%${searchTerm}%`,
    `%${searchTerm}%`,
    `%${searchTerm}%`,
  ])
}

// Airports
export async function getAirports() {
  return queryMany("SELECT * FROM airports ORDER BY name")
}

export async function searchAirports(searchTerm: string) {
  return queryMany("SELECT * FROM airports WHERE name LIKE ? OR code LIKE ? OR city LIKE ? ORDER BY name", [
    `%${searchTerm}%`,
    `%${searchTerm}%`,
    `%${searchTerm}%`,
  ])
}

// Users
export async function getUserProfiles() {
  return queryMany(`
    SELECT up.*, a.name as airline_name, a.iata_code 
    FROM user_profiles up 
    LEFT JOIN airlines a ON up.airline_id = a.id 
    ORDER BY up.created_at DESC
  `)
}

export async function getUserProfileById(id: string) {
  return queryOne(
    `
    SELECT up.*, a.name as airline_name, a.iata_code 
    FROM user_profiles up 
    LEFT JOIN airlines a ON up.airline_id = a.id 
    WHERE up.id = ?
  `,
    [id],
  )
}

export async function updateUserApproval(userId: string, isApproved: boolean) {
  return executeQuery("UPDATE user_profiles SET is_approved = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", [
    isApproved,
    userId,
  ])
}

// Flight Requests
export async function getFlightRequests() {
  return queryMany(`
    SELECT fr.*, 
           up.full_name as user_name,
           a1.name as airline_name, a1.iata_code as airline_code,
           ap1.name as from_airport_name, ap1.code as from_airport_code,
           ap2.name as to_airport_name, ap2.code as to_airport_code,
           a2.name as traveler_airline_name
    FROM flight_requests fr
    LEFT JOIN user_profiles up ON fr.user_id = up.id
    LEFT JOIN airlines a1 ON fr.airline_id = a1.id
    LEFT JOIN airports ap1 ON fr.from_airport_id = ap1.id
    LEFT JOIN airports ap2 ON fr.to_airport_id = ap2.id
    LEFT JOIN airlines a2 ON fr.traveler_airline_affiliation_id = a2.id
    ORDER BY fr.created_at DESC
  `)
}

export async function getFlightRequestsByAirline(airlineId: number) {
  return queryMany(
    `
    SELECT fr.*, 
           up.full_name as user_name,
           a1.name as airline_name, a1.iata_code as airline_code,
           ap1.name as from_airport_name, ap1.code as from_airport_code,
           ap2.name as to_airport_name, ap2.code as to_airport_code,
           a2.name as traveler_airline_name
    FROM flight_requests fr
    LEFT JOIN user_profiles up ON fr.user_id = up.id
    LEFT JOIN airlines a1 ON fr.airline_id = a1.id
    LEFT JOIN airports ap1 ON fr.from_airport_id = ap1.id
    LEFT JOIN airports ap2 ON fr.to_airport_id = ap2.id
    LEFT JOIN airlines a2 ON fr.traveler_airline_affiliation_id = a2.id
    WHERE fr.airline_id = ?
    ORDER BY fr.created_at DESC
  `,
    [airlineId],
  )
}

// Integrations
export async function getIntegrations() {
  return queryMany("SELECT * FROM integrations ORDER BY name")
}

export async function updateIntegration(id: number, data: any) {
  return executeQuery(
    "UPDATE integrations SET api_url = ?, api_key = ?, is_connected = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
    [data.api_url, data.api_key, data.is_connected, id],
  )
}
