"use client"

import { useState } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { User, Lock, Calendar, Building, CheckCircle, Clock, ExternalLink } from "lucide-react"
import { PasswordUpdateForm } from "./password-update-form"

interface UserProfileProps {
  profile: {
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
    created_at: string
    airline_name?: string
    iata_code?: string
  }
  userEmail: string
}

export function UserProfile({ profile, userEmail }: UserProfileProps) {
  const [isPasswordDialogOpen, setIsPasswordDialogOpen] = useState(false)

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
    })
  }

  return (
    <div className="space-y-6">
      {/* Account Status Alert */}
      {!profile.is_approved && (
        <Alert>
          <Clock className="h-4 w-4" />
          <AlertDescription>
            Your account is pending approval. You will receive an email notification once approved.
          </AlertDescription>
        </Alert>
      )}

      {profile.is_approved && (
        <Alert className="border-green-200 bg-green-50">
          <CheckCircle className="h-4 w-4 text-green-600" />
          <AlertDescription className="text-green-800">Your account has been approved and is active.</AlertDescription>
        </Alert>
      )}

      {/* Basic Information */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <User className="h-5 w-5" />
            Basic Information
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label className="text-sm font-medium text-gray-500">Full Name</Label>
              <Input value={profile.full_name} readOnly className="bg-gray-50" />
            </div>
            <div>
              <Label className="text-sm font-medium text-gray-500">Username</Label>
              <Input value={profile.username} readOnly className="bg-gray-50" />
            </div>
            <div>
              <Label className="text-sm font-medium text-gray-500">Email</Label>
              <Input value={userEmail} readOnly className="bg-gray-50" />
            </div>
            <div>
              <Label className="text-sm font-medium text-gray-500">Status</Label>
              <div className="mt-2">
                <Badge
                  variant={profile.status === "retired" ? "secondary" : "default"}
                  className={profile.status === "retired" ? "bg-yellow-100 text-yellow-800" : ""}
                >
                  {profile.status === "active" ? "Active Employee" : "Retired"}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Airline Information */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Building className="h-5 w-5" />
            Airline Information
          </CardTitle>
        </CardHeader>
        <CardContent>
          {profile.airline_name ? (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label className="text-sm font-medium text-gray-500">Airline Name</Label>
                <Input value={profile.airline_name} readOnly className="bg-gray-50" />
              </div>
              <div>
                <Label className="text-sm font-medium text-gray-500">IATA Code</Label>
                <Input value={profile.iata_code || "N/A"} readOnly className="bg-gray-50" />
              </div>
            </div>
          ) : (
            <p className="text-gray-500">No airline information available</p>
          )}
        </CardContent>
      </Card>

      {/* Retirement Information (if applicable) */}
      {profile.status === "retired" && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              Retirement Information
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {profile.phone_number && (
                <div>
                  <Label className="text-sm font-medium text-gray-500">Phone Number</Label>
                  <Input value={profile.phone_number} readOnly className="bg-gray-50" />
                </div>
              )}
              {profile.retirement_date && (
                <div>
                  <Label className="text-sm font-medium text-gray-500">Retirement Date</Label>
                  <Input value={formatDate(profile.retirement_date)} readOnly className="bg-gray-50" />
                </div>
              )}
              {profile.ex_airline_job && (
                <div>
                  <Label className="text-sm font-medium text-gray-500">Previous Job Title</Label>
                  <Input value={profile.ex_airline_job} readOnly className="bg-gray-50" />
                </div>
              )}
              {profile.years_worked && (
                <div>
                  <Label className="text-sm font-medium text-gray-500">Years of Service</Label>
                  <Input value={`${profile.years_worked} years`} readOnly className="bg-gray-50" />
                </div>
              )}
            </div>

            {profile.retired_id_url && (
              <div>
                <Label className="text-sm font-medium text-gray-500">Retirement ID Document</Label>
                <div className="mt-2">
                  <Button variant="outline" size="sm" asChild>
                    <a href={profile.retired_id_url} target="_blank" rel="noopener noreferrer">
                      <ExternalLink className="h-4 w-4 mr-2" />
                      View Document
                    </a>
                  </Button>
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      )}

      {/* Account Settings */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Lock className="h-5 w-5" />
            Account Settings
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div>
              <Label className="text-sm font-medium text-gray-500">Password</Label>
              <div className="flex items-center gap-4 mt-2">
                <Input type="password" value="••••••••" readOnly className="bg-gray-50 flex-1" />
                <Dialog open={isPasswordDialogOpen} onOpenChange={setIsPasswordDialogOpen}>
                  <DialogTrigger asChild>
                    <Button variant="outline">Change Password</Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Change Password</DialogTitle>
                    </DialogHeader>
                    <PasswordUpdateForm onSuccess={() => setIsPasswordDialogOpen(false)} />
                  </DialogContent>
                </Dialog>
              </div>
            </div>

            <div>
              <Label className="text-sm font-medium text-gray-500">Member Since</Label>
              <Input value={formatDate(profile.created_at)} readOnly className="bg-gray-50" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
