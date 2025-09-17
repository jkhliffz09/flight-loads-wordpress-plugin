import { type NextRequest, NextResponse } from "next/server"
import { queryOne } from "@/lib/mysql/connection"

export async function POST(request: NextRequest) {
  try {
    const { email } = await request.json()

    if (!email) {
      return NextResponse.json({ error: "Email is required" }, { status: 400 })
    }

    // Check if email exists in users table
    const existingUser = await queryOne("SELECT id FROM users WHERE email = ?", [email])

    return NextResponse.json({ exists: !!existingUser })
  } catch (error) {
    console.error("Error checking email:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
