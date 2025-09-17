import mysql from "mysql2/promise"

// Check if MySQL environment variables are available
export const isMySQLConfigured = typeof process.env.DATABASE_URL === "string" && process.env.DATABASE_URL.length > 0

// Create MySQL connection pool
let pool: mysql.Pool | null = null

export function getPool() {
  if (!pool && isMySQLConfigured) {
    pool = mysql.createPool({
      uri: process.env.DATABASE_URL,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0,
      acquireTimeout: 60000,
      timeout: 60000,
    })
  }
  return pool
}

// Execute query with connection pool
export async function executeQuery(query: string, params: any[] = []) {
  if (!isMySQLConfigured) {
    throw new Error("MySQL is not configured")
  }

  const connection = getPool()
  if (!connection) {
    throw new Error("Failed to create database connection")
  }

  try {
    const [results] = await connection.execute(query, params)
    return results
  } catch (error) {
    console.error("Database query error:", error)
    throw error
  }
}

// Get single record
export async function queryOne(query: string, params: any[] = []) {
  const results = (await executeQuery(query, params)) as any[]
  return results[0] || null
}

// Get multiple records
export async function queryMany(query: string, params: any[] = []) {
  const results = (await executeQuery(query, params)) as any[]
  return results
}
