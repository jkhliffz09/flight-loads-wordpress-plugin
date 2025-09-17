import { type NextRequest, NextResponse } from "next/server"
import { executeQuery } from "@/lib/mysql/connection"
import { hashPassword } from "@/lib/auth/session"
import { v4 as uuidv4 } from "uuid"

export async function POST(request: NextRequest) {
  try {
    const formData = await request.formData()

    const fullName = formData.get("fullName") as string
    const username = formData.get("username") as string
    const email = formData.get("email") as string
    const password = formData.get("password") as string
    const airlineId = formData.get("airlineId") as string
    const status = formData.get("status") as "active" | "retired"

    // Retired user fields
    const phoneNumber = formData.get("phoneNumber") as string
    const retirementDate = formData.get("retirementDate") as string
    const exAirlineJob = formData.get("exAirlineJob") as string
    const yearsWorked = formData.get("yearsWorked") as string
    const retiredIdFile = formData.get("retiredIdFile") as File

    if (!fullName || !username || !email || !password || !status) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 })
    }

    // Check if email or username already exists
    const existingUser = await executeQuery(
      "SELECT id FROM users WHERE email = ? UNION SELECT id FROM user_profiles WHERE username = ?",
      [email, username],
    )

    if (Array.isArray(existingUser) && existingUser.length > 0) {
      return NextResponse.json({ error: "Email or username already exists" }, { status: 400 })
    }

    // Hash password
    const hashedPassword = await hashPassword(password)
    const userId = uuidv4()

    // Create user
    await executeQuery("INSERT INTO users (id, email, password_hash) VALUES (?, ?, ?)", [userId, email, hashedPassword])

    // Create user profile
    const profileData = [
      userId,
      fullName,
      username,
      airlineId ? Number.parseInt(airlineId) : null,
      status,
      status === "retired" ? phoneNumber : null,
      status === "retired" && retirementDate ? new Date(retirementDate).toISOString().split("T")[0] : null,
      status === "retired" ? exAirlineJob : null,
      status === "retired" && yearsWorked ? Number.parseInt(yearsWorked) : null,
      null, // retired_id_url - would handle file upload here
      false, // is_approved
      false, // is_admin
    ]

    await executeQuery(
      `INSERT INTO user_profiles 
       (id, full_name, username, airline_id, status, phone_number, retirement_date, 
        ex_airline_job, years_worked, retired_id_url, is_approved, is_admin) 
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      profileData,
    )

    // TODO: Handle file upload for retired ID
    // TODO: Send notification emails

    return NextResponse.json({ success: true, userId })
  } catch (error) {
    console.error("Error creating account:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
