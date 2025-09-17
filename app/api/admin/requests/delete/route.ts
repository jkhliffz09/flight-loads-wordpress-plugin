import { type NextRequest, NextResponse } from "next/server"
import { getCurrentUser } from "@/lib/auth/session"
import { executeQuery } from "@/lib/mysql/connection"

export async function POST(request: NextRequest) {
  try {
    // Check if user is admin
    const { user, profile } = await getCurrentUser()

    if (!user || !profile || !profile.is_admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const { requestId } = await request.json()

    if (!requestId) {
      return NextResponse.json({ error: "Request ID is required" }, { status: 400 })
    }

    // Delete the flight request
    await executeQuery("DELETE FROM flight_requests WHERE id = ?", [requestId])

    return NextResponse.json({ success: true })
  } catch (error) {
    console.error("Error deleting flight request:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
