<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Fuel Event</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editFueling">Edit</v-btn>
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
      <v-card-text>
        <FuelingOverviewComponent
          v-if="!loading"
          :fueling="fueling"
          v-model="fueling"
        />
      </v-card-text>
    </v-card>

    <ManageFuelingComponent
      v-if="showEditFueling"
      :fueling="fueling"
      :vehicle="fueling.vehicle"
      v-model="showEditFueling"
      @fueling-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import FuelingOverviewComponent from '~/components/fuelings/Overview'
import ManageFuelingComponent from '~/components/fuelings/ManageFueling'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    FuelingOverviewComponent,
    ManageFuelingComponent,
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditFueling: false,
    fueling: {
      fuel_unit_type:{name: null},
      fuel_type:{name: null}
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.fueling = await this.$axios.$get(`fuelings/${this.$route.params.id}`)
      this.loading = false
    },
    editFueling () {
      this.showEditFueling = true
    },
    async deleteItem () {
      await this.$axios.$delete(`fuelings/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
