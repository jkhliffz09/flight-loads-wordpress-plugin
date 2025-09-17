import { type NextRequest, NextResponse } from "next/server"
import { getAirlineById } from "@/lib/mysql/database"

export async function GET(request: NextRequest, { params }: { params: { id: string } }) {
  try {
    const airlineId = Number.parseInt(params.id)

    if (isNaN(airlineId)) {
      return NextResponse.json({ error: "Invalid airline ID" }, { status: 400 })
    }

    const airline = await getAirlineById(airlineId)

    if (!airline) {
      return NextResponse.json({ error: "Airline not found" }, { status: 404 })
    }

    return NextResponse.json(airline)
  } catch (error) {
    console.error("Error fetching airline:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
