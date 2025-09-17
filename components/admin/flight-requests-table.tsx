"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { Edit, Trash2, Eye } from "lucide-react"
import { useRouter } from "next/navigation"

interface FlightRequest {
  id: number
  flight_number: string
  travel_date: string
  is_return: boolean
  return_flight_number?: string
  return_travel_date?: string
  notes?: string
  status: string
  created_at: string
  user_name: string
  airline_name: string
  airline_code: string
  from_airport_name: string
  from_airport_code: string
  to_airport_name: string
  to_airport_code: string
  traveler_airline_name: string
}

interface FlightRequestsTableProps {
  requests: FlightRequest[]
}

export function FlightRequestsTable({ requests }: FlightRequestsTableProps) {
  const [selectedRequest, setSelectedRequest] = useState<FlightRequest | null>(null)
  const [loading, setLoading] = useState(false)
  const router = useRouter()

  const handleDelete = async (requestId: number) => {
    if (!confirm("Are you sure you want to delete this flight request?")) return

    setLoading(true)
    try {
      const response = await fetch("/api/admin/requests/delete", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ requestId }),
      })

      if (!response.ok) {
        throw new Error("Failed to delete request")
      }

      router.refresh()
    } catch (error) {
      console.error("Error deleting request:", error)
    } finally {
      setLoading(false)
    }
  }

  const formatRequestTitle = (request: FlightRequest) => {
    const outbound = `${request.airline_code}${request.flight_number} ${request.from_airport_code}-${request.to_airport_code}`
    if (request.is_return && request.return_flight_number) {
      const returnFlight = `${request.airline_code}${request.return_flight_number} ${request.to_airport_code}-${request.from_airport_code}`
      return `${outbound} / ${returnFlight}`
    }
    return outbound
  }

  return (
    <div className="bg-white rounded-lg shadow">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Request Title</TableHead>
            <TableHead>Author</TableHead>
            <TableHead>Travel Date</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Created</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {requests.map((request) => (
            <TableRow key={request.id}>
              <TableCell className="font-medium">{formatRequestTitle(request)}</TableCell>
              <TableCell>
                <div>
                  <p className="font-medium">{request.user_name}</p>
                </div>
              </TableCell>
              <TableCell>{new Date(request.travel_date).toLocaleDateString()}</TableCell>
              <TableCell>
                <Badge variant={request.status === "pending" ? "secondary" : "default"}>{request.status}</Badge>
              </TableCell>
              <TableCell>{new Date(request.created_at).toLocaleDateString()}</TableCell>
              <TableCell>
                <div className="flex gap-2">
                  <Dialog>
                    <DialogTrigger asChild>
                      <Button variant="outline" size="sm" onClick={() => setSelectedRequest(request)}>
                        <Eye className="h-4 w-4" />
                      </Button>
                    </DialogTrigger>
                    <DialogContent className="max-w-2xl">
                      <DialogHeader>
                        <DialogTitle>Flight Request Details</DialogTitle>
                      </DialogHeader>
                      {selectedRequest && <FlightRequestDetails request={selectedRequest} />}
                    </DialogContent>
                  </Dialog>
                  <Button variant="outline" size="sm">
                    <Edit className="h-4 w-4" />
                  </Button>
                  <Button variant="outline" size="sm" onClick={() => handleDelete(request.id)} disabled={loading}>
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

function FlightRequestDetails({ request }: { request: FlightRequest }) {
  return (
    <div className="space-y-4">
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="text-sm font-medium text-gray-500">Flight</label>
          <p className="text-sm text-gray-900">
            {request.airline_name} ({request.airline_code}
            {request.flight_number})
          </p>
        </div>
        <div>
          <label className="text-sm font-medium text-gray-500">Route</label>
          <p className="text-sm text-gray-900">
            {request.from_airport_code} → {request.to_airport_code}
          </p>
        </div>
        <div>
          <label className="text-sm font-medium text-gray-500">Travel Date</label>
          <p className="text-sm text-gray-900">{new Date(request.travel_date).toLocaleDateString()}</p>
        </div>
        <div>
          <label className="text-sm font-medium text-gray-500">Traveler Airline</label>
          <p className="text-sm text-gray-900">{request.traveler_airline_name}</p>
        </div>
      </div>

      {request.is_return && (
        <div className="border-t pt-4">
          <h3 className="font-medium text-gray-900 mb-2">Return Flight</h3>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-sm font-medium text-gray-500">Return Flight</label>
              <p className="text-sm text-gray-900">
                {request.airline_code}
                {request.return_flight_number}
              </p>
            </div>
            <div>
              <label className="text-sm font-medium text-gray-500">Return Route</label>
              <p className="text-sm text-gray-900">
                {request.to_airport_code} → {request.from_airport_code}
              </p>
            </div>
            <div>
              <label className="text-sm font-medium text-gray-500">Return Date</label>
              <p className="text-sm text-gray-900">
                {request.return_travel_date ? new Date(request.return_travel_date).toLocaleDateString() : "N/A"}
              </p>
            </div>
          </div>
        </div>
      )}

      {request.notes && (
        <div className="border-t pt-4">
          <label className="text-sm font-medium text-gray-500">Notes</label>
          <p className="text-sm text-gray-900 mt-1">{request.notes}</p>
        </div>
      )}
    </div>
  )
}
