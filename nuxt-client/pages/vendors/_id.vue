<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Vendor</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editVendor">Edit</v-btn>
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
        {{ vendor.name }}
      </v-card-title>
      <v-card-text>
        <VendorOverviewComponent
          v-if="!loading"
          :vendor="vendor"
          v-model="vendor"
        />
      </v-card-text>
    </v-card>

    <ManageVendorComponent
      v-if="showEditVendor"
      :vendor="vendor"
      v-model="showEditVendor"
      @vendor-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import VendorOverviewComponent from '~/components/vendors/Overview'
import ManageVendorComponent from '~/components/vendors/ManageVendor'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    VendorOverviewComponent,
    ManageVendorComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditVendor: false,
    vendor: {
      name: null,
      abbrv: null,
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.vendor = await this.$axios.$get(`vendors/${this.$route.params.id}`)
      this.loading = false
    },
    editVendor () {
      this.showEditVendor = true
    },
    async deleteItem () {
      await this.$axios.$delete(`vendors/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
