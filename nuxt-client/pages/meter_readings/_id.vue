<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Meter Reading</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editMeterReading">Edit</v-btn>
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
        {{ meterReading.name }}
      </v-card-title>
      <v-card-text>
        <MeterReadingOverviewComponent
          v-if="!loading"
          :meterReading="meterReading"
          v-model="meterReading"
        />
      </v-card-text>
    </v-card>

    <ManageMeterReadingComponent
      v-if="showEditMeterReading"
      :meterReading="meterReading"
      :vehicle="meterReading.vehicle"
      v-model="showEditMeterReading"
      @meterReading-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import MeterReadingOverviewComponent from '~/components/meter_readings/Overview'
import ManageMeterReadingComponent from '~/components/meter_readings/ManageMeterReading'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    MeterReadingOverviewComponent,
    ManageMeterReadingComponent,
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditMeterReading: false,
    meterReading: {
      name: null,
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.meterReading = await this.$axios.$get(`meter_readings/${this.$route.params.id}`)
      this.loading = false
    },
    editMeterReading () {
      this.showEditMeterReading = true
    },
    async deleteItem () {
      await this.$axios.$delete(`meter_readings/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
