<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Preventive Maintenance Dashboard</h2>
        </v-col>
        <v-col>
          <v-btn small color="info" style="float: right" @click="addPreventiveMaintenance">Add Preventive Maintenance</v-btn>
        </v-col>
      </v-row>
    </div>

    <PreventiveMaintenancesGridComponent
      v-if="showPreventiveMaintenances"
      :vehicle="vehicle"
      :showAddPreventiveMaintenanceInGrid="showAddPreventiveMaintenanceInGrid"
      :showVehicleInGrid = "showVehicleInGrid"
      v-model="showPreventiveMaintenances"
    />

    <ManagePreventiveMaintenanceComponent
      v-if="addingPreventiveMaintenance"
      v-model="addingPreventiveMaintenance"
      :allowVehicleAssignment = "allowVehicleAssignment"
      :vehicle="vehicle"
      @preventive_maintenance-saved="refresh"
    />


  </div>
</template>

<script>

import PreventiveMaintenancesGridComponent from '~/components/preventive_maintenances/PreventiveMaintenancesGrid'
import ManagePreventiveMaintenanceComponent from '~/components/preventive_maintenances/ManagePreventiveMaintenance'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    PreventiveMaintenancesGridComponent,
    ManagePreventiveMaintenanceComponent
  },
  data: () => ({
    addingPreventiveMaintenance: false,
    showPreventiveMaintenances: true,
    showAddPreventiveMaintenanceInGrid: false,
    showVehicleInGrid: true,
    allowVehicleAssignment: true,
    vehicle: null,
  }),
  methods: {
    addPreventiveMaintenance () {
      this.addingPreventiveMaintenance = true
    },
    refresh () {
      this.showPreventiveMaintenances = false
      this.$nextTick().then(() => {
        // Add the component back in
        this.showPreventiveMaintenances = true
      });
    }
  }
}
</script>
