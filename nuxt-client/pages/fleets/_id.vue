<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Fleet</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editFleet"  v-if="!loading && pCreateEditDelete">Edit</v-btn>
          <v-dialog
            v-model="deleteDialog"
            persistent
            max-width="290"
            v-if="!loading && pCreateEditDelete"
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

    <v-card>
      <v-card-title>
        {{ fleet.name }}
      </v-card-title>
      <v-card-text>
        <FleetBreadcrumbs
          v-if="!loading"
          :fleetEntity="fleet"
          :fleetEntityType="'Fleet'"
          style="padding: 0px"
        />
        <FleetOverviewComponent
          v-if="!loading"
          :fleet="fleet"
          v-model="fleet"
        />
      </v-card-text>
    </v-card>

    <ManageFleetComponent
      v-if="showEditFleet"
      :fleet="fleet"
      v-model="showEditFleet"
      @fleet-saved="getDataFromApi"
    />

    <VehiclesGridComponent
      v-if="showVehicles"
      :fleet="fleet"
      :showAddVehicleInGrid="showAddVehicleInGrid"
      v-model="showVehicles"
    />


    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import FleetOverviewComponent from '~/components/fleets/Overview'
import ManageFleetComponent from '~/components/fleets/ManageFleet'
import VehiclesGridComponent from '~/components/vehicles/VehiclesGrid'
import FleetBreadcrumbs from '~/components/breadcrumbs/FleetBreadcrumb'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    FleetOverviewComponent,
    ManageFleetComponent,
    VehiclesGridComponent,
    FleetBreadcrumbs
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditFleet: false,
    showVehicles: false,
    showAddVehicleInGrid: true,
    fleet: {
      name: null,
    },
    pCreateEditDelete: false
  }),
  async created () {
    if (this.$laravel.hasPermission('manage any fleet')) {
      this.pCreateEditDelete = true
    }
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.fleet = await this.$axios.$get(`fleets/${this.$route.params.id}`)
      this.loading = false
      this.showVehicles = true //Causes grid component to render after data is retrieved
    },
    editFleet () {
      this.showEditFleet = true
    },
    async deleteItem () {
      await this.$axios.$delete(`fleets/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
