<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Engine Manufacturer</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editEngineManufacturer">Edit</v-btn>
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
        {{ engine_manufacturer.name }}
      </v-card-title>
      <v-card-text>
        <EngineManufacturerOverviewComponent
          v-if="!loading"
          :engine_manufacturer="engine_manufacturer"
          v-model="engine_manufacturer"
        />
      </v-card-text>
    </v-card>

    <ManageEngineManufacturerComponent
      v-if="showEditEngineManufacturer"
      :engine_manufacturer="engine_manufacturer"
      v-model="showEditEngineManufacturer"
      @engine_manufacturer-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import EngineManufacturerOverviewComponent from '~/components/engine_manufacturers/Overview'
import ManageEngineManufacturerComponent from '~/components/engine_manufacturers/ManageEngineManufacturer'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    EngineManufacturerOverviewComponent,
    ManageEngineManufacturerComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditEngineManufacturer: false,
    engine_manufacturer: {
      name: null,
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.engine_manufacturer = await this.$axios.$get(`engine_manufacturers/${this.$route.params.id}`)
      this.loading = false
    },
    editEngineManufacturer () {
      this.showEditEngineManufacturer = true
    },
    async deleteItem () {
      await this.$axios.$delete(`engine_manufacturers/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
