"use client"

import type React from "react"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { Label } from "@/components/ui/label"
import { Plus, Edit, Trash2 } from "lucide-react"
import { useRouter } from "next/navigation"

interface Airline {
  id: number
  name: string
  iata_code: string
  domain: string
  created_at: string
}

interface AirlinesManagementProps {
  airlines: Airline[]
  userCounts: Record<number, number>
}

export function AirlinesManagement({ airlines, userCounts }: AirlinesManagementProps) {
  const [isAddDialogOpen, setIsAddDialogOpen] = useState(false)
  const [editingAirline, setEditingAirline] = useState<Airline | null>(null)
  const [loading, setLoading] = useState(false)
  const router = useRouter()

  const [formData, setFormData] = useState({
    name: "",
    iata_code: "",
    domain: "",
  })

  const resetForm = () => {
    setFormData({ name: "", iata_code: "", domain: "" })
    setEditingAirline(null)
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)

    try {
      if (editingAirline) {
        const response = await fetch("/api/admin/airlines", {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            id: editingAirline.id,
            ...formData,
          }),
        })

        if (!response.ok) {
          throw new Error("Failed to update airline")
        }
      } else {
        const response = await fetch("/api/admin/airlines", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        })

        if (!response.ok) {
          throw new Error("Failed to add airline")
        }
      }

      resetForm()
      setIsAddDialogOpen(false)
      router.refresh()
    } catch (error) {
      console.error("Error saving airline:", error)
    } finally {
      setLoading(false)
    }
  }

  const handleEdit = (airline: Airline) => {
    setEditingAirline(airline)
    setFormData({
      name: airline.name,
      iata_code: airline.iata_code,
      domain: airline.domain,
    })
    setIsAddDialogOpen(true)
  }

  const handleDelete = async (airlineId: number) => {
    if (!confirm("Are you sure you want to delete this airline?")) return

    setLoading(true)
    try {
      const response = await fetch("/api/admin/airlines", {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id: airlineId }),
      })

      if (!response.ok) {
        throw new Error("Failed to delete airline")
      }

      router.refresh()
    } catch (error) {
      console.error("Error deleting airline:", error)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="bg-white rounded-lg shadow">
      <div className="p-6 border-b">
        <div className="flex justify-between items-center">
          <h2 className="text-xl font-semibold">Airline Domain Management</h2>
          <Dialog
            open={isAddDialogOpen}
            onOpenChange={(open) => {
              setIsAddDialogOpen(open)
              if (!open) resetForm()
            }}
          >
            <DialogTrigger asChild>
              <Button>
                <Plus className="h-4 w-4 mr-2" />
                Add Airline
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>{editingAirline ? "Edit Airline" : "Add New Airline"}</DialogTitle>
              </DialogHeader>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <Label htmlFor="name">Airline Name</Label>
                  <Input
                    id="name"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    required
                  />
                </div>
                <div>
                  <Label htmlFor="iata_code">IATA Code</Label>
                  <Input
                    id="iata_code"
                    value={formData.iata_code}
                    onChange={(e) => setFormData({ ...formData, iata_code: e.target.value.toUpperCase() })}
                    maxLength={3}
                    required
                  />
                </div>
                <div>
                  <Label htmlFor="domain">Domain</Label>
                  <Input
                    id="domain"
                    value={formData.domain}
                    onChange={(e) => setFormData({ ...formData, domain: e.target.value })}
                    placeholder="example.com"
                    required
                  />
                </div>
                <div className="flex justify-end gap-2">
                  <Button type="button" variant="outline" onClick={() => setIsAddDialogOpen(false)}>
                    Cancel
                  </Button>
                  <Button type="submit" disabled={loading}>
                    {editingAirline ? "Update" : "Add"} Airline
                  </Button>
                </div>
              </form>
            </DialogContent>
          </Dialog>
        </div>
      </div>

      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Airline Name</TableHead>
            <TableHead>IATA Code</TableHead>
            <TableHead>Domain</TableHead>
            <TableHead>Associated Users</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {airlines.map((airline) => (
            <TableRow key={airline.id}>
              <TableCell className="font-medium">{airline.name}</TableCell>
              <TableCell>{airline.iata_code}</TableCell>
              <TableCell>{airline.domain}</TableCell>
              <TableCell>{userCounts[airline.id] || 0} users</TableCell>
              <TableCell>
                <div className="flex gap-2">
                  <Button variant="outline" size="sm" onClick={() => handleEdit(airline)}>
                    <Edit className="h-4 w-4" />
                  </Button>
                  <Button variant="outline" size="sm" onClick={() => handleDelete(airline.id)} disabled={loading}>
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
