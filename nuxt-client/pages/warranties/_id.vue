<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Warranty</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editWarranty">Edit</v-btn>
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
        {{ warranty.name }}
      </v-card-title>
      <v-card-text>
        <WarrantyOverviewComponent
          v-if="!loading"
          :warranty="warranty"
          v-model="warranty"
        />
      </v-card-text>
    </v-card>

    <ManageWarrantyComponent
      v-if="showEditWarranty"
      :warranty="warranty"
      :vehicle="warranty.vehicle"
      v-model="showEditWarranty"
      @warranty-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import WarrantyOverviewComponent from '~/components/warranties/Overview'
import ManageWarrantyComponent from '~/components/warranties/ManageWarranty'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    WarrantyOverviewComponent,
    ManageWarrantyComponent,
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditWarranty: false,
    warranty: {
      name: null,
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.warranty = await this.$axios.$get(`warranties/${this.$route.params.id}`)
      this.loading = false
    },
    editWarranty () {
      this.showEditWarranty = true
    },
    async deleteItem () {
      await this.$axios.$delete(`warranties/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
