<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Vehicle Type</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editVehicleType">Edit</v-btn>
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

    <v-card>
      <v-card-title>
        {{ vehicle_type.name }}
      </v-card-title>
      <v-card-text>
        <VehicleTypeOverviewComponent
          v-if="!loading"
          :vehicle_type="vehicle_type"
          v-model="vehicle_type"
        />
      </v-card-text>
    </v-card>

    <ManageVehicleTypeComponent
      v-if="showEditVehicleType"
      :vehicle_type="vehicle_type"
      v-model="showEditVehicleType"
      @vehicle_type-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import VehicleTypeOverviewComponent from '~/components/vehicle_types/Overview'
import ManageVehicleTypeComponent from '~/components/vehicle_types/ManageVehicleType'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    VehicleTypeOverviewComponent,
    ManageVehicleTypeComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditVehicleType: false,
    vehicle_type: {
      name: null,
      desc: null,
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.vehicle_type = await this.$axios.$get(`vehicle_types/${this.$route.params.id}`)
      this.loading = false
    },
    editVehicleType () {
      this.showEditVehicleType = true
    },
    async deleteItem () {
      await this.$axios.$delete(`vehicle_types/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
