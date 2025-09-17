import { getCurrentUser } from "@/lib/auth/session"
import { getUserProfileById } from "@/lib/mysql/database"
import { redirect } from "next/navigation"
import { UserProfile } from "@/components/account/user-profile"

export default async function AccountPage() {
  const { user, profile } = await getCurrentUser()

  if (!user || !profile) {
    redirect("/auth/login")
  }

  try {
    // Get detailed profile with airline information
    const detailedProfile = await getUserProfileById(user.id)

    if (!detailedProfile) {
      redirect("/apply")
    }

    return (
      <div className="min-h-screen bg-gray-50">
        <div className="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
          <div className="mb-8">
            <h1 className="text-3xl font-bold text-gray-900">My Account</h1>
            <p className="text-gray-600 mt-2">Manage your profile and account settings</p>
          </div>

          <UserProfile profile={detailedProfile} userEmail={user.email} />
        </div>
      </div>
    )
  } catch (error) {
    console.error("Error fetching user profile:", error)
    return <div>Error loading profile</div>
  }
}
