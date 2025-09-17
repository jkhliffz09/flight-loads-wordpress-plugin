"use server"

import { executeQuery, queryOne } from "@/lib/mysql/connection"
import { hashPassword, verifyPassword, createSession, deleteSession, getCurrentUser } from "@/lib/auth/session"
import { redirect } from "next/navigation"
import { v4 as uuidv4 } from "uuid"

export async function signIn(prevState: any, formData: FormData) {
  if (!formData) {
    return { error: "Form data is missing" }
  }

  const email = formData.get("email")
  const password = formData.get("password")

  if (!email || !password) {
    return { error: "Email and password are required" }
  }

  try {
    // Find user by email
    const user = await queryOne("SELECT * FROM users WHERE email = ?", [email.toString()])

    if (!user) {
      return { error: "Invalid email or password" }
    }

    // Verify password
    const isValidPassword = await verifyPassword(password.toString(), user.password_hash)
    if (!isValidPassword) {
      return { error: "Invalid email or password" }
    }

    // Check if user profile is approved
    const profile = await queryOne("SELECT * FROM user_profiles WHERE id = ?", [user.id])

    if (profile && !profile.is_approved) {
      return { error: "Your account is pending approval. Please wait for admin approval." }
    }

    // Create session
    await createSession(user.id)

    return { success: true }
  } catch (error) {
    console.error("Login error:", error)
    return { error: "An unexpected error occurred. Please try again." }
  }
}

export async function signUp(prevState: any, formData: FormData) {
  if (!formData) {
    return { error: "Form data is missing" }
  }

  const email = formData.get("email")
  const password = formData.get("password")
  const username = formData.get("username")
  const fullName = formData.get("fullName")

  if (!email || !password || !username || !fullName) {
    return { error: "All fields are required" }
  }

  try {
    // Check if user already exists
    const existingUser = await queryOne("SELECT id FROM users WHERE email = ?", [email.toString()])

    if (existingUser) {
      return { error: "User with this email already exists" }
    }

    // Check if username is taken
    const existingUsername = await queryOne("SELECT id FROM user_profiles WHERE username = ?", [username.toString()])

    if (existingUsername) {
      return { error: "Username is already taken" }
    }

    // Hash password
    const hashedPassword = await hashPassword(password.toString())
    const userId = uuidv4()

    // Create user
    await executeQuery("INSERT INTO users (id, email, password_hash) VALUES (?, ?, ?)", [
      userId,
      email.toString(),
      hashedPassword,
    ])

    // Create user profile (will be populated later in application form)
    await executeQuery(
      "INSERT INTO user_profiles (id, full_name, username, status, is_approved) VALUES (?, ?, ?, ?, ?)",
      [userId, fullName.toString(), username.toString(), "active", false],
    )

    return { success: "Account created successfully. Please complete your application." }
  } catch (error) {
    console.error("Sign up error:", error)
    return { error: "An unexpected error occurred. Please try again." }
  }
}

export async function signOut() {
  await deleteSession()
  redirect("/auth/login")
}

export async function updatePassword(prevState: any, formData: FormData) {
  if (!formData) {
    return { error: "Form data is missing" }
  }

  const currentPassword = formData.get("currentPassword")
  const newPassword = formData.get("newPassword")
  const confirmPassword = formData.get("confirmPassword")

  if (!currentPassword || !newPassword || !confirmPassword) {
    return { error: "All fields are required" }
  }

  if (newPassword !== confirmPassword) {
    return { error: "New passwords do not match" }
  }

  try {
    // Get current user (you'll need to implement getCurrentUser)
    const { user } = await getCurrentUser()
    if (!user) {
      return { error: "Not authenticated" }
    }

    // Verify current password
    const isValidPassword = await verifyPassword(currentPassword.toString(), user.password_hash)
    if (!isValidPassword) {
      return { error: "Current password is incorrect" }
    }

    // Hash new password
    const hashedPassword = await hashPassword(newPassword.toString())

    // Update password
    await executeQuery("UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", [
      hashedPassword,
      user.id,
    ])

    return { success: "Password updated successfully" }
  } catch (error) {
    console.error("Password update error:", error)
    return { error: "An unexpected error occurred. Please try again." }
  }
}
