<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Preventive Maintenance Item</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn v-if="!preventive_maintenance.repair_order" small class="ml-5" color="info" style="float: right" @click="addRepairOrder">Create Repair Order</v-btn>
          <v-btn small color="info" style="float: right" @click="editPreventiveMaintenance">Edit</v-btn>
          <v-dialog
            v-model="deleteDialog"
            persistent
            max-width="290"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-btn small
                     class="mr-5"
                     color="#fe8181"
                     style="float:right"
                     v-bind="attrs"
                     v-on="on">
                Delete
              </v-btn>
            </template>
            <v-card>
              <v-card-title class="headline">Are you sure?</v-card-title>
              <v-card-text>Do you really want to delete?</v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="green darken-1" text @click="deleteDialog = false">Cancel</v-btn>
                <v-btn color="red" @click="deleteItem">Delete</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

        </v-col>
      </v-row>
    </div>

    <ManageRepairOrderComponent
      v-if="addingRepairOrder"
      v-model="addingRepairOrder"
      :allowVehicleAssignment = "false"
      :vehicle="preventive_maintenance.vehicle"
      :linkToPM="preventive_maintenance"
      @repair_order-saved="refresh"
    />

    <v-card>
      <v-card-title>
        {{ preventive_maintenance.name }}
      </v-card-title>
      <v-card-text>
        <FleetBreadcrumbs
          v-if="!loading"
          :fleetEntity="preventive_maintenance"
          :fleetEntityType="'PM'"
          style="padding: 0px"
        />
        <PreventiveMaintenanceOverviewComponent
          v-if="!loading"
          :preventive_maintenance="preventive_maintenance"
          v-model="preventive_maintenance"
        />
      </v-card-text>
    </v-card>

    <ManagePreventiveMaintenanceComponent
      v-if="showEditPreventiveMaintenance"
      :preventive_maintenance="preventive_maintenance"
      :vehicle="preventive_maintenance.vehicle"
      :pm_template="preventive_maintenance.preventive_maintenance_template"
      :allowVehicleAssignment="allowVehicleAssignment"
      v-model="showEditPreventiveMaintenance"
      @preventive_maintenance-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import PreventiveMaintenanceOverviewComponent from '~/components/preventive_maintenances/Overview'
import ManagePreventiveMaintenanceComponent from '~/components/preventive_maintenances/ManagePreventiveMaintenance'
import ManageRepairOrderComponent from '~/components/repair_orders/ManageRepairOrder'
import FleetBreadcrumbs from '~/components/breadcrumbs/FleetBreadcrumb'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    PreventiveMaintenanceOverviewComponent,
    ManagePreventiveMaintenanceComponent,
    ManageRepairOrderComponent,
    FleetBreadcrumbs
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditPreventiveMaintenance: false,
    allowVehicleAssignment: false,
    preventive_maintenance: {
      name: null,
      desc: null
    },
    addingRepairOrder: false
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi (options) {
      this.loading = true
      this.preventive_maintenance = await this.$axios.$get(`preventive_maintenances/${this.$route.params.id}`)
      this.loading = false
    },
    editPreventiveMaintenance () {
      this.showEditPreventiveMaintenance = true
    },
    async deleteItem () {
      await this.$axios.$delete(`preventive_maintenances/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    goBack () {
      this.$router.back()
    },
    addRepairOrder () {
      this.addingRepairOrder = true
    },
    refresh (roNumber) {
      this.getDataFromApi()
    }

  }
}
</script>
