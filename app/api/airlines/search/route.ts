import { type NextRequest, NextResponse } from "next/server"
import { searchAirlines } from "@/lib/mysql/database"

export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url)
    const query = searchParams.get("q")

    if (!query || query.length < 2) {
      return NextResponse.json([])
    }

    const airlines = await searchAirlines(query)
    return NextResponse.json(airlines)
  } catch (error) {
    console.error("Error searching airlines:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
