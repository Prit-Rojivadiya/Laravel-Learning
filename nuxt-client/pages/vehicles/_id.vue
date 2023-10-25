<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Vehicle</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn v-if="!loading" small color="info" style="float: right" @click="editVehicle">Edit</v-btn>
          <v-dialog
            v-model="deleteDialog"
            persistent
            max-width="290"
            v-if="!loading"
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

    <div>
      <v-card v-if="vehicle && vehicle.id" class="mt-5">
        <v-card-title style="word-break: break-word">Vehicle Unit Number: {{ vehicle.vehicle_number }}  |  Fleet: {{ vehicle.fleet.name}} </v-card-title>
        <FleetBreadcrumbs
          v-if="vehicle"
          :fleetEntity="vehicle"
          :fleetEntityType="'Vehicle'"
          style="padding: 0px 0px 0px 16px"
        />
        <v-tabs v-model="default_tab" show-arrows >
          <v-tabs-slider color="teal lighten-3"></v-tabs-slider>
          <v-tab key="overview">Overview</v-tab>
          <v-tab key="meters">Meter Readings</v-tab>
          <v-tab key="fuelings">Fuel Events</v-tab>
          <v-tab key="pm">PM</v-tab>
          <v-tab key="repair-orders">Repair Orders</v-tab>
          <v-tab key="warranty">Warranty</v-tab>

          <v-tab-item key="overview">
            <v-card-text>
              <VehicleOverviewComponent v-if="!loading" :vehicle="vehicle" v-model="vehicle"/>
            </v-card-text>
          </v-tab-item>
          <v-tab-item key="meters">
            <v-card-text>
              <VehicleMeterReadingsGridComponent
                v-if="!loading"
                :vehicle="vehicle"
                v-model="vehicle"
                :showAddMeterReadingInGrid="true"
              />
            </v-card-text>
          </v-tab-item>
          <v-tab-item key="fuelings">
            <v-card-text>
              <VehicleFuelingsGridComponent
                v-if="!loading"
                :vehicle="vehicle"
                v-model="vehicle"
                :showAddFuelingInGrid="true"
              />
            </v-card-text>
          </v-tab-item>
          <v-tab-item key="pm">
            <v-card-text>
              <PreventiveMaintenancesGridComponent
                v-if="!loading"
                :vehicle="vehicle"
                v-model="vehicle"
                :showVehicleInGrid="false"
                :allowVehicleAssignment="false"
                :showAddPreventiveMaintenanceInGrid="true"
              />
            </v-card-text>
          </v-tab-item>
          <v-tab-item key="repair-orders">
            <v-card-text>
              <RepairOrdersGridComponent
                v-if="!loading"
                :vehicle="vehicle"
                v-model="vehicle"
                :showVehicleInGrid="false"
                :allowVehicleAssignment="false"
                :showAddRepairOrderInGrid="true"
              />
            </v-card-text>
          </v-tab-item>
          <v-tab-item key="warranty">
            <v-card-text>
              <VehicleWarrantiesGridComponent
                v-if="!loading"
                :vehicle="vehicle"
                v-model="vehicle"
                :showAddWarrantyInGrid="true"
              />
            </v-card-text>
          </v-tab-item>
        </v-tabs>

      </v-card>
    </div>

    <ManageVehicleComponent
      v-if="showEditVehicle"
      :vehicle="vehicle"
      v-model="showEditVehicle"
      @vehicle-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import VehicleOverviewComponent from '~/components/vehicles/Overview'
import VehicleWarrantiesGridComponent from '~/components/warranties/WarrantiesGrid'
import VehicleMeterReadingsGridComponent from '~/components/meter_readings/MeterReadingsGrid'
import VehicleFuelingsGridComponent from '~/components/fuelings/FuelingsGrid'
import PreventiveMaintenancesGridComponent from '~/components/preventive_maintenances/PreventiveMaintenancesGrid'
import RepairOrdersGridComponent from '~/components/repair_orders/RepairOrdersGrid'
import ManageVehicleComponent from '~/components/vehicles/ManageVehicle'
import FleetBreadcrumbs from '~/components/breadcrumbs/FleetBreadcrumb'



export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    VehicleOverviewComponent,
    VehicleWarrantiesGridComponent,
    VehicleMeterReadingsGridComponent,
    VehicleFuelingsGridComponent,
    PreventiveMaintenancesGridComponent,
    RepairOrdersGridComponent,
    ManageVehicleComponent,
    FleetBreadcrumbs
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditVehicle: false,
    showVehicles: false,
    showAddVehicleInGrid: true,
    vehicle: {
      vehicle_number: null,
    },
    default_tab: 'overview',
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.vehicle = await this.$axios.$get(`vehicles/${this.$route.params.id}`)
      this.loading = false
      this.showVehicles = true //Causes grid component to render after data is retrieved
    },
    editVehicle () {
      this.showEditVehicle = true
    },
    async deleteItem () {
      await this.$axios.$delete(`vehicles/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>

<style scoped>

</style>

