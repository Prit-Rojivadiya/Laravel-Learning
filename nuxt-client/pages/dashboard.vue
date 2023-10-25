<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <div>
      <h2>Home Dashboard</h2>
    </div>

<!--    <div class="mb-2 mt-4">-->
<!--      <span>Select from the following, or navigate the drawer menu for more options.</span>-->
<!--&lt;!&ndash;      ROW COLUMN&ndash;&gt;-->
<!--    </div>-->

    <v-row dense class="px-4">
      <v-col lg="4" sm="4" xs="12" align-items="center" class="tz-grid-item" dense>
        <div class="d-flex flex-column justify-space-between align-center text-h6 mb-1" @click="itemClicked('CPMSummary')" style="cursor: pointer">
          Cost Per Mile Summary
          <v-img
            class="mt-1"
            src="/img/dashboard/cpm_summary.jpeg"
            max-width="200"
          ></v-img>
        </div>
      </v-col>
      <v-col lg="4" sm="4" xs="12" align-items="center" class="tz-grid-item" dense>
        <div class="d-flex flex-column justify-space-between align-center text-h6 mb-1" @click="itemClicked('CPMDetailed')" style="cursor: pointer">
          Cost Per Mile Details
          <v-img
            class="mt-2"
            src="/img/dashboard/cpm_details.jpeg"
            max-width="90%"
          ></v-img>
        </div>
      </v-col>
      <v-col lg="4" sm="4" xs="12" align-items="center" class="tz-grid-item" dense>
        <div class="d-flex flex-column justify-space-between align-center text-h6 mb-1" @click="itemClicked('InvoiceSummary')" style="cursor: pointer">
          Invoice Summary
          <v-img
            class="mt-5"
            src="/img/dashboard/invoice_summay.jpeg"
            max-width="90%"
          ></v-img>
        </div>
      </v-col>
      <v-col lg="4" sm="4" xs="12" align-items="center" class="tz-grid-item" dense>
        <div class="d-flex flex-column justify-space-between align-center text-h6 mb-1" @click="itemClicked('PM')" style="cursor: pointer">
          Preventive Maintenance
          <v-img
            class="mt-2"
            src="/img/dashboard/preventive_maintenance.jpg"
            max-width="200"
          ></v-img>
        </div>
      </v-col>
      <v-col lg="4" sm="4" xs="12" align-items="center" class="tz-grid-item" dense>
        <div class="d-flex flex-column justify-space-between align-center text-h6 mb-1" @click="itemClicked('RO')" style="cursor: pointer">
          Repair Orders
          <v-img
            class="mt-2"
            src="/img/dashboard/repair_orders.png"
            max-width="150"
          ></v-img>
        </div>
      </v-col>
      <v-col lg="4" sm="4" xs="12" align-items="center" class="tz-grid-item" dense>
        <div class="d-flex flex-column justify-space-between align-center text-h6 mb-1" @click="itemClicked('ManageFleets')" style="cursor: pointer">
          Manage Fleets
          <v-img
            class="tz-dashboard-image-mf"
            src="/img/dashboard/fleet_management.png"
            max-width="300"
          ></v-img>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

export default {
  middleware: 'auth',
  layout: 'loggedin',
  data: () => ({
  }),
  created () {

  },
  methods: {
    itemClicked (item) {
      switch (item) {
        case 'CPMSummary':
          this.$router.push(`/reports/cost_per_mile_summary`)
          break
        case 'CPMDetailed':
          this.$router.push(`/reports/cost_per_mile_detailed`)
          break
        case 'InvoiceSummary':
          this.$router.push(`/reports/invoice_summary`)
          break
        case 'PM':
          this.$router.push(`/preventive_maintenances`)
          break
        case 'RO':
          this.$router.push(`/repair_orders`)
          break
        case 'ManageFleets':
          this.$router.push(`/clients`)
          break
      }
    }
  }
}
</script>

<style scoped>
.tz-grid-item {
  text-align: center;
  padding-right: 15px;
  margin-bottom: 15px;
  margin-top: 30px;
  white-space: nowrap;
  min-width: 250px;
}

.tz-dashboard-image-mf {
  margin-top: -65px;
}
</style>
