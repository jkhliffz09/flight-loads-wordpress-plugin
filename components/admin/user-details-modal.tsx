"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Check, X, ExternalLink } from "lucide-react"
import { useRouter } from "next/navigation"

interface User {
  id: string
  full_name: string
  username: string
  status: "active" | "retired"
  is_approved: boolean
  phone_number?: string
  retirement_date?: string
  ex_airline_job?: string
  years_worked?: number
  retired_id_url?: string
  airline_name?: string
  iata_code?: string
  created_at: string
}

interface UserDetailsModalProps {
  user: User
}

export function UserDetailsModal({ user }: UserDetailsModalProps) {
  const [loading, setLoading] = useState(false)
  const router = useRouter()

  const handleApproval = async (approved: boolean) => {
    setLoading(true)
    try {
      const response = await fetch("/api/admin/users/approve", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          userId: user.id,
          isApproved: approved,
        }),
      })

      if (!response.ok) {
        throw new Error("Failed to update user approval")
      }

      // Refresh the page to show updated data
      router.refresh()
    } catch (error) {
      console.error("Error updating user approval:", error)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="text-sm font-medium text-gray-500">Full Name</label>
          <p className="text-sm text-gray-900">{user.full_name}</p>
        </div>
        <div>
          <label className="text-sm font-medium text-gray-500">Username</label>
          <p className="text-sm text-gray-900">{user.username}</p>
        </div>
        <div>
          <label className="text-sm font-medium text-gray-500">Status</label>
          <div>
            <Badge
              variant={user.status === "retired" ? "destructive" : "default"}
              className={user.status === "retired" ? "bg-yellow-100 text-yellow-800" : ""}
            >
              {user.status}
            </Badge>
          </div>
        </div>
        <div>
          <label className="text-sm font-medium text-gray-500">Airline</label>
          <p className="text-sm text-gray-900">
            {user.airline_name ? `${user.airline_name} (${user.iata_code})` : "N/A"}
          </p>
        </div>
      </div>

      {user.status === "retired" && (
        <div className="border-t pt-4">
          <h3 className="text-lg font-medium text-gray-900 mb-4 bg-yellow-50 p-2 rounded">Retirement Information</h3>
          <div className="grid grid-cols-2 gap-4">
            {user.phone_number && (
              <div>
                <label className="text-sm font-medium text-gray-500">Phone Number</label>
                <p className="text-sm text-gray-900">{user.phone_number}</p>
              </div>
            )}
            {user.retirement_date && (
              <div>
                <label className="text-sm font-medium text-gray-500">Retirement Date</label>
                <p className="text-sm text-gray-900">{new Date(user.retirement_date).toLocaleDateString()}</p>
              </div>
            )}
            {user.ex_airline_job && (
              <div>
                <label className="text-sm font-medium text-gray-500">Ex-Airline Job</label>
                <p className="text-sm text-gray-900">{user.ex_airline_job}</p>
              </div>
            )}
            {user.years_worked && (
              <div>
                <label className="text-sm font-medium text-gray-500">Years Worked</label>
                <p className="text-sm text-gray-900">{user.years_worked} years</p>
              </div>
            )}
          </div>
          {user.retired_id_url && (
            <div className="mt-4">
              <label className="text-sm font-medium text-gray-500">Retired ID Document</label>
              <div className="mt-1">
                <Button variant="outline" size="sm" asChild>
                  <a href={user.retired_id_url} target="_blank" rel="noopener noreferrer">
                    <ExternalLink className="h-4 w-4 mr-1" />
                    View Document
                  </a>
                </Button>
              </div>
            </div>
          )}
        </div>
      )}

      <div className="border-t pt-4">
        <div className="flex justify-between items-center">
          <div>
            <label className="text-sm font-medium text-gray-500">Approval Status</label>
            <div className="mt-1">
              <Badge variant={user.is_approved ? "default" : "secondary"}>
                {user.is_approved ? "Approved" : "Pending Approval"}
              </Badge>
            </div>
          </div>

          {!user.is_approved && (
            <div className="flex gap-2">
              <Button onClick={() => handleApproval(false)} variant="outline" size="sm" disabled={loading}>
                <X className="h-4 w-4 mr-1" />
                Deny
              </Button>
              <Button onClick={() => handleApproval(true)} size="sm" disabled={loading}>
                <Check className="h-4 w-4 mr-1" />
                Approve
              </Button>
            </div>
          )}
        </div>
      </div>

      <div className="text-xs text-gray-500">
        <p>User ID: {user.id}</p>
        <p>Created: {new Date(user.created_at).toLocaleString()}</p>
      </div>
    </div>
  )
}
