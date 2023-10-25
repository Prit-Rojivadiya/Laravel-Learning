<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Fleets Dashboard</h2>
        </v-col>
        <v-col>
          <v-btn small color="info" style="float: right" @click="addFleet"  v-if="pCreateEditDelete">Add Fleet</v-btn>
        </v-col>
      </v-row>
    </div>

    <FleetsGridComponent
      v-if="showFleets"
      :branch="branch"
      :showAddFleetInGrid="showAddFleetInGrid"
      :showBranchInGrid = "showBranchInGrid"
      v-model="showFleets"
    />

    <ManageFleetComponent
      v-if="addingFleet"
      v-model="addingFleet"
      :branch="branch"
      @fleet-saved="refresh"
    />


  </div>
</template>

<script>

import FleetsGridComponent from '~/components/fleets/FleetsGrid'
import ManageFleetComponent from '~/components/fleets/ManageFleet'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    FleetsGridComponent,
    ManageFleetComponent
  },
  data: () => ({
    addingFleet: false,
    showFleets: true,
    showAddFleetInGrid: false,
    showBranchInGrid: true,
    branch: null,
    pCreateEditDelete: false
  }),
  created () {
    if (this.$laravel.hasPermission('manage any fleet')) {
      this.pCreateEditDelete = true
    }
  },
  methods: {
    addFleet () {
      this.addingFleet = true
    },
    refresh () {
      this.showFleets = false
      this.$nextTick().then(() => {
        // Add the component back in
        this.showFleets = true
      });
    }
  }
}
</script>
