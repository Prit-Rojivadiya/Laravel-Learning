<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Name:</span>
            <span>{{ preventive_maintenance_template.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Recurring:</span>
            <span>{{ preventive_maintenance_template.recurring }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Repair Order Type:</span>
            <span>{{ preventive_maintenance_template.repair_order_type.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Description:</span>
            <span>{{ preventive_maintenance_template.desc }}</span>
          </div>
        </div>
      </v-flex>

      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Meter Type:</span>
            <span>{{ preventive_maintenance_template.system_meter_type.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Length {{ preventive_maintenance_template.system_meter_type.name }}:</span>
            <span>{{ preventive_maintenance_template.length_meters }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Length Days:</span>
            <span>{{ preventive_maintenance_template.length_days }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">PM Schedule Due Type:</span>
            <span>{{ preventive_maintenance_template.system_p_m_due_type.name }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "PreventiveMaintenanceTemplateOverview",
  props: ['value', 'preventive_maintenance_template'],
  data: () => ({
    working: false,
  }),
  async created () {
  },
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
