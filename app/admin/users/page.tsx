import { getUserProfiles } from "@/lib/mysql/database"
import { getCurrentUser } from "@/lib/auth/session"
import { redirect } from "next/navigation"
import { UsersTable } from "@/components/admin/users-table"

export default async function AdminUsersPage() {
  const { user, profile } = await getCurrentUser()

  if (!user || !profile || !profile.is_admin) {
    redirect("/auth/login")
  }

  try {
    const users = await getUserProfiles()

    return (
      <div>
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Users Management</h1>
          <p className="text-gray-600 mt-2">Manage user accounts and approvals</p>
        </div>

        <UsersTable users={users || []} />
      </div>
    )
  } catch (error) {
    console.error("Error fetching users:", error)
    return <div>Error loading users</div>
  }
}
