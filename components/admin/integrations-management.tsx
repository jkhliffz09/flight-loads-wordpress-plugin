"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Label } from "@/components/ui/label"
import { Plug, Settings } from "lucide-react"
import { supabase } from "@/lib/supabase/client"
import { useRouter } from "next/navigation"

interface Integration {
  id: number
  name: string
  type: string
  api_url?: string
  api_key?: string
  is_connected: boolean
  created_at: string
}

interface IntegrationsManagementProps {
  integrations: Integration[]
}

export function IntegrationsManagement({ integrations }: IntegrationsManagementProps) {
  const [editingIntegration, setEditingIntegration] = useState<Integration | null>(null)
  const [loading, setLoading] = useState(false)
  const router = useRouter()

  const [formData, setFormData] = useState({
    api_url: "",
    api_key: "",
  })

  const handleConnect = async (integration: Integration) => {
    if (!formData.api_url || !formData.api_key) {
      alert("Please provide both API URL and API Key")
      return
    }

    setLoading(true)
    try {
      const { error } = await supabase
        .from("integrations")
        .update({
          api_url: formData.api_url,
          api_key: formData.api_key,
          is_connected: true,
        })
        .eq("id", integration.id)

      if (error) throw error

      setEditingIntegration(null)
      setFormData({ api_url: "", api_key: "" })
      router.refresh()
    } catch (error) {
      console.error("Error connecting integration:", error)
    } finally {
      setLoading(false)
    }
  }

  const handleDisconnect = async (integrationId: number) => {
    if (!confirm("Are you sure you want to disconnect this integration?")) return

    setLoading(true)
    try {
      const { error } = await supabase
        .from("integrations")
        .update({
          api_url: null,
          api_key: null,
          is_connected: false,
        })
        .eq("id", integrationId)

      if (error) throw error

      router.refresh()
    } catch (error) {
      console.error("Error disconnecting integration:", error)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {integrations.map((integration) => (
        <Card key={integration.id}>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle className="flex items-center gap-2">
                <Plug className="h-5 w-5" />
                {integration.name}
              </CardTitle>
              <Badge variant={integration.is_connected ? "default" : "secondary"}>
                {integration.is_connected ? "Connected" : "Disconnected"}
              </Badge>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div>
                <p className="text-sm text-gray-600">Type: {integration.type}</p>
                {integration.is_connected && integration.api_url && (
                  <p className="text-sm text-gray-600">URL: {integration.api_url}</p>
                )}
              </div>

              {editingIntegration?.id === integration.id ? (
                <div className="space-y-3">
                  <div>
                    <Label htmlFor={`api_url_${integration.id}`}>API URL</Label>
                    <Input
                      id={`api_url_${integration.id}`}
                      value={formData.api_url}
                      onChange={(e) => setFormData({ ...formData, api_url: e.target.value })}
                      placeholder="https://api.example.com"
                    />
                  </div>
                  <div>
                    <Label htmlFor={`api_key_${integration.id}`}>API Key</Label>
                    <Input
                      id={`api_key_${integration.id}`}
                      type="password"
                      value={formData.api_key}
                      onChange={(e) => setFormData({ ...formData, api_key: e.target.value })}
                      placeholder="Enter API key"
                    />
                  </div>
                  <div className="flex gap-2">
                    <Button size="sm" onClick={() => handleConnect(integration)} disabled={loading}>
                      Connect
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => {
                        setEditingIntegration(null)
                        setFormData({ api_url: "", api_key: "" })
                      }}
                    >
                      Cancel
                    </Button>
                  </div>
                </div>
              ) : (
                <div className="flex gap-2">
                  {integration.is_connected ? (
                    <>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => {
                          setEditingIntegration(integration)
                          setFormData({
                            api_url: integration.api_url || "",
                            api_key: integration.api_key || "",
                          })
                        }}
                      >
                        <Settings className="h-4 w-4 mr-1" />
                        Configure
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleDisconnect(integration.id)}
                        disabled={loading}
                      >
                        Disconnect
                      </Button>
                    </>
                  ) : (
                    <Button
                      size="sm"
                      onClick={() => {
                        setEditingIntegration(integration)
                        setFormData({ api_url: "", api_key: "" })
                      }}
                    >
                      <Plug className="h-4 w-4 mr-1" />
                      Connect
                    </Button>
                  )}
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      ))}
    </div>
  )
}
