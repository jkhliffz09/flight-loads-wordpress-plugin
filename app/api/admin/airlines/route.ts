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

    const { name, iata_code, domain } = await request.json()

    if (!name || !iata_code || !domain) {
      return NextResponse.json({ error: "All fields are required" }, { status: 400 })
    }

    // Add new airline
    await executeQuery("INSERT INTO airlines (name, iata_code, domain) VALUES (?, ?, ?)", [name, iata_code, domain])

    return NextResponse.json({ success: true })
  } catch (error) {
    console.error("Error adding airline:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}

export async function PUT(request: NextRequest) {
  try {
    // Check if user is admin
    const { user, profile } = await getCurrentUser()

    if (!user || !profile || !profile.is_admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const { id, name, iata_code, domain } = await request.json()

    if (!id || !name || !iata_code || !domain) {
      return NextResponse.json({ error: "All fields are required" }, { status: 400 })
    }

    // Update airline
    await executeQuery("UPDATE airlines SET name = ?, iata_code = ?, domain = ? WHERE id = ?", [
      name,
      iata_code,
      domain,
      id,
    ])

    return NextResponse.json({ success: true })
  } catch (error) {
    console.error("Error updating airline:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}

export async function DELETE(request: NextRequest) {
  try {
    // Check if user is admin
    const { user, profile } = await getCurrentUser()

    if (!user || !profile || !profile.is_admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const { id } = await request.json()

    if (!id) {
      return NextResponse.json({ error: "Airline ID is required" }, { status: 400 })
    }

    // Delete airline
    await executeQuery("DELETE FROM airlines WHERE id = ?", [id])

    return NextResponse.json({ success: true })
  } catch (error) {
    console.error("Error deleting airline:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
