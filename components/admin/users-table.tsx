"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { Eye } from "lucide-react"
import { UserDetailsModal } from "./user-details-modal"

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

interface UsersTableProps {
  users: User[]
}

export function UsersTable({ users }: UsersTableProps) {
  const [selectedUser, setSelectedUser] = useState<User | null>(null)

  return (
    <div className="bg-white rounded-lg shadow">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>ID</TableHead>
            <TableHead>Name</TableHead>
            <TableHead>Username</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Airline</TableHead>
            <TableHead>Approved</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {users.map((user) => (
            <TableRow key={user.id}>
              <TableCell className="font-mono text-sm">{user.id.slice(0, 8)}...</TableCell>
              <TableCell className="font-medium">{user.full_name}</TableCell>
              <TableCell>{user.username}</TableCell>
              <TableCell>
                <Badge
                  variant={user.status === "retired" ? "destructive" : "default"}
                  className={user.status === "retired" ? "bg-yellow-100 text-yellow-800" : ""}
                >
                  {user.status}
                </Badge>
              </TableCell>
              <TableCell>{user.airline_name ? `${user.airline_name} (${user.iata_code})` : "N/A"}</TableCell>
              <TableCell>
                <Badge variant={user.is_approved ? "default" : "secondary"}>
                  {user.is_approved ? "Approved" : "Pending"}
                </Badge>
              </TableCell>
              <TableCell>
                <Dialog>
                  <DialogTrigger asChild>
                    <Button variant="outline" size="sm" onClick={() => setSelectedUser(user)}>
                      <Eye className="h-4 w-4 mr-1" />
                      View
                    </Button>
                  </DialogTrigger>
                  <DialogContent className="max-w-2xl">
                    <DialogHeader>
                      <DialogTitle>User Details</DialogTitle>
                    </DialogHeader>
                    {selectedUser && <UserDetailsModal user={selectedUser} />}
                  </DialogContent>
                </Dialog>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
