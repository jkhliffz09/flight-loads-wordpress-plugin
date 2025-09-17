import { cookies } from "next/headers"
import { executeQuery, queryOne } from "@/lib/mysql/connection"
import { SignJWT, jwtVerify } from "jose"
import bcrypt from "bcryptjs"

const JWT_SECRET = new TextEncoder().encode(process.env.JWT_SECRET || "your-secret-key-change-this-in-production")

export interface User {
  id: string
  email: string
  password_hash: string // Added password_hash property
  email_verified: boolean
  created_at: string
  updated_at: string
}

export interface UserProfile {
  id: string
  full_name: string
  username: string
  airline_id: number | null
  status: "active" | "retired"
  phone_number: string | null
  retirement_date: string | null
  ex_airline_job: string | null
  years_worked: number | null
  retired_id_url: string | null
  is_approved: boolean
  is_admin: boolean
  created_at: string
  updated_at: string
}

// Create JWT token
export async function createToken(userId: string) {
  const token = await new SignJWT({ userId })
    .setProtectedHeader({ alg: "HS256" })
    .setIssuedAt()
    .setExpirationTime("7d")
    .sign(JWT_SECRET)

  return token
}

// Verify JWT token
export async function verifyToken(token: string) {
  try {
    const { payload } = await jwtVerify(token, JWT_SECRET)
    return payload.userId as string
  } catch (error) {
    return null
  }
}

// Get current user from session
export async function getCurrentUser(): Promise<{ user: User | null; profile: UserProfile | null }> {
  try {
    const cookieStore = cookies()
    const token = cookieStore.get("session-token")?.value

    if (!token) {
      return { user: null, profile: null }
    }

    const userId = await verifyToken(token)
    if (!userId) {
      return { user: null, profile: null }
    }

    // Get user data
    const user = (await queryOne("SELECT * FROM users WHERE id = ?", [userId])) as User | null

    if (!user) {
      return { user: null, profile: null }
    }

    // Get user profile
    const profile = (await queryOne("SELECT * FROM user_profiles WHERE id = ?", [userId])) as UserProfile | null

    return { user, profile }
  } catch (error) {
    console.error("Error getting current user:", error)
    return { user: null, profile: null }
  }
}

// Hash password
export async function hashPassword(password: string): Promise<string> {
  return bcrypt.hash(password, 12)
}

// Verify password
export async function verifyPassword(password: string, hashedPassword: string): Promise<boolean> {
  return bcrypt.compare(password, hashedPassword)
}

// Create user session
export async function createSession(userId: string) {
  const token = await createToken(userId)
  const expiresAt = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000) // 7 days

  // Store session in database
  await executeQuery("INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)", [
    userId,
    token,
    expiresAt,
  ])

  // Set cookie
  const cookieStore = cookies()
  cookieStore.set("session-token", token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax",
    expires: expiresAt,
    path: "/",
  })

  return token
}

// Delete user session
export async function deleteSession() {
  const cookieStore = cookies()
  const token = cookieStore.get("session-token")?.value

  if (token) {
    // Remove from database
    await executeQuery("DELETE FROM user_sessions WHERE session_token = ?", [token])
  }

  // Clear cookie
  cookieStore.delete("session-token")
}

// Clean expired sessions
export async function cleanExpiredSessions() {
  await executeQuery("DELETE FROM user_sessions WHERE expires_at < NOW()")
}
