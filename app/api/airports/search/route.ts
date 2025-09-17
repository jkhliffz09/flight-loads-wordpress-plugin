import { type NextRequest, NextResponse } from "next/server"
import { searchAirports } from "@/lib/mysql/database"

export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url)
    const query = searchParams.get("q")

    if (!query || query.length < 2) {
      return NextResponse.json([])
    }

    const airports = await searchAirports(query)
    return NextResponse.json(airports)
  } catch (error) {
    console.error("Error searching airports:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
