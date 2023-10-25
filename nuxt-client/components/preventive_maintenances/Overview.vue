<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Vehicle Unit Number:</span>
            <span>{{ preventive_maintenance.vehicle.vehicle_number }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">PM Name:</span>
            <span>{{ preventive_maintenance.name }}</span>
          </div>
<!--          <div class="flex-item">-->
<!--            <span class="grey&#45;&#45;text">PM Template:</span>-->
<!--            <span>{{ preventive_maintenance.preventive_maintenance_template.name }}</span>-->
<!--          </div>-->
          <div class="flex-item">
            <span class="grey--text">Recurring:</span>
            <span>{{ preventive_maintenance.recurring | formatYesNo }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Repair Order Type:</span>
            <span>{{ preventive_maintenance.repair_order_type.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">PM Schedule Due Type:</span>
            <span>{{ preventive_maintenance.system_p_m_due_type.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Meter Type:</span>
            <span>{{ preventive_maintenance.system_meter_type.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Length {{ preventive_maintenance.system_meter_type.name }}:</span>
            <span>{{ preventive_maintenance.length_meters  | formatNumber}}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Length Days:</span>
            <span>{{ preventive_maintenance.length_days }}</span>
          </div>
        </div>
      </v-flex>

      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Start At Meter:</span>
            <span>{{ preventive_maintenance.start_at_meter  | formatNumber}}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Due At Meter:</span>
            <span>{{ preventive_maintenance.due_at_meter  | formatNumber}}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Completed At Meter:</span>
            <span v-if="preventive_maintenance.completed_at_meter">{{ preventive_maintenance.completed_at_meter | formatNumber}}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Repair Order:</span>
            <span>{{ ro_number}}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Vehicle Latest Meter Reading:</span>
            <span>{{ preventive_maintenance.latestMeterReading | formatNumber }}</span>
          </div>
        </div>
      </v-flex>

      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Start Date:</span>
            <span>{{ preventive_maintenance.start_date | formatDateMDY }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Due Date:</span>
            <span>{{ preventive_maintenance.due_date | formatDateMDY }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Completed Date:</span>
            <span>{{ preventive_maintenance.completed_date | formatDateMDY }}</span>
          </div>
        </div>
      </v-flex>

    </v-layout>
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Description:</span>
            <span>{{ preventive_maintenance.desc }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "PreventiveMaintenanceOverview",
  props: ['value', 'preventive_maintenance'],
  data: () => ({
    working: false,
    ro_number: null,
  }),
  async created () {
    if(this.preventive_maintenance.repair_order) {
      this.ro_number = this.preventive_maintenance.repair_order.ro_number
    }
  },
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
