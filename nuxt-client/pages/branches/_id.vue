<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Branch</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editBranch"  v-if="!loading && pCreateEditDelete">Edit</v-btn>
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
        {{ branch.name }}
      </v-card-title>
      <v-card-text>
        <FleetBreadcrumbs
          v-if="!loading"
          :fleetEntity="branch"
          :fleetEntityType="'Branch'"
          style="padding: 0px"
        />
        <BranchOverviewComponent
          v-if="!loading"
          :branch="branch"
          v-model="branch"
        />
      </v-card-text>
    </v-card>

    <ManageBranchComponent
      v-if="showEditBranch"
      :branch="branch"
      v-model="showEditBranch"
      @branch-saved="getDataFromApi"
    />

    <FleetsGridComponent
      v-if="showFleets"
      :branch="branch"
      :showAddFleetInGrid="showAddFleetInGrid"
      v-model="showFleets"
    />


    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import BranchOverviewComponent from '~/components/branches/Overview'
import ManageBranchComponent from '~/components/branches/ManageBranch'
import FleetsGridComponent from '~/components/fleets/FleetsGrid'
import FleetBreadcrumbs from '~/components/breadcrumbs/FleetBreadcrumb'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    BranchOverviewComponent,
    ManageBranchComponent,
    FleetsGridComponent,
    FleetBreadcrumbs
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditBranch: false,
    showFleets: false,
    showAddFleetInGrid: true,
    branch: {
      name: null,
    },
    pCreateEditDelete: false
  }),
  async created () {
    if (this.$laravel.hasPermission('manage any branch')) {
      this.pCreateEditDelete = true
    }
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.branch = await this.$axios.$get(`branches/${this.$route.params.id}`)
      this.loading = false
      this.showFleets = true //Causes grid component to render after data is retrieved
    },
    editBranch () {
      this.showEditBranch = true
    },
    async deleteItem () {
      await this.$axios.$delete(`branches/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
