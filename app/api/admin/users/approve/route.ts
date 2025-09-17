import { type NextRequest, NextResponse } from "next/server"
import { getCurrentUser } from "@/lib/auth/session"
import { updateUserApproval } from "@/lib/mysql/database"

export async function POST(request: NextRequest) {
  try {
    // Check if user is admin
    const { user, profile } = await getCurrentUser()

    if (!user || !profile || !profile.is_admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const { userId, isApproved } = await request.json()

    if (!userId || typeof isApproved !== "boolean") {
      return NextResponse.json({ error: "Invalid request data" }, { status: 400 })
    }

    // Update user approval status
    await updateUserApproval(userId, isApproved)

    return NextResponse.json({ success: true })
  } catch (error) {
    console.error("Error updating user approval:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
