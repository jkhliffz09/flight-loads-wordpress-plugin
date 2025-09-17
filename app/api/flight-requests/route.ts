import { type NextRequest, NextResponse } from "next/server"
import { getCurrentUser } from "@/lib/auth/session"
import { executeQuery, queryMany } from "@/lib/mysql/connection"

export async function POST(request: NextRequest) {
  try {
    // Check if user is authenticated
    const { user, profile } = await getCurrentUser()

    if (!user || !profile || !profile.is_approved) {
      return NextResponse.json({ error: "Unauthorized or not approved" }, { status: 401 })
    }

    const {
      airlineId,
      flightNumber,
      fromAirportId,
      toAirportId,
      travelDate,
      isReturn,
      returnFlightNumber,
      returnFromAirportId,
      returnToAirportId,
      returnTravelDate,
      travelerAirlineId,
      notes,
    } = await request.json()

    if (!airlineId || !flightNumber || !fromAirportId || !toAirportId || !travelDate || !travelerAirlineId) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 })
    }

    // Check for duplicate requests in the last hour
    const oneHourAgo = new Date(Date.now() - 60 * 60 * 1000)
    const duplicates = await queryMany(
      `SELECT id FROM flight_requests 
       WHERE airline_id = ? AND flight_number = ? AND from_airport_id = ? 
       AND to_airport_id = ? AND travel_date = ? AND created_at >= ?`,
      [airlineId, flightNumber, fromAirportId, toAirportId, travelDate, oneHourAgo],
    )

    if (duplicates.length > 0) {
      return NextResponse.json(
        { error: "A similar request was made within the last hour. Please check existing requests." },
        { status: 400 },
      )
    }

    // Create the flight request
    await executeQuery(
      `INSERT INTO flight_requests 
       (user_id, airline_id, flight_number, from_airport_id, to_airport_id, travel_date, 
        is_return, return_flight_number, return_from_airport_id, return_to_airport_id, 
        return_travel_date, traveler_airline_affiliation_id, notes) 
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        user.id,
        airlineId,
        flightNumber,
        fromAirportId,
        toAirportId,
        travelDate,
        isReturn,
        isReturn ? returnFlightNumber : null,
        isReturn ? returnFromAirportId : null,
        isReturn ? returnToAirportId : null,
        isReturn ? returnTravelDate : null,
        travelerAirlineId,
        notes,
      ],
    )

    return NextResponse.json({ success: true })
  } catch (error) {
    console.error("Error creating flight request:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
