import { createClient } from "@/lib/supabase/server"
import { IntegrationsManagement } from "@/components/admin/integrations-management"

export default async function AdminIntegrationsPage() {
  const supabase = createClient()

  const { data: integrations, error } = await supabase.from("integrations").select("*").order("name")

  if (error) {
    console.error("Error fetching integrations:", error)
    return <div>Error loading integrations</div>
  }

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">Integrations</h1>
        <p className="text-gray-600 mt-2">Manage third-party integrations and API connections</p>
      </div>

      <IntegrationsManagement integrations={integrations || []} />
    </div>
  )
}
