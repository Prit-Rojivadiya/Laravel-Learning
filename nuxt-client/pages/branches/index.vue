<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Branches Dashboard</h2>
        </v-col>
        <v-col>
          <v-btn small color="info" style="float: right" @click="addBranch" v-if="pCreateEditDelete">Add Branch</v-btn>
        </v-col>
      </v-row>
    </div>

    <BranchesGridComponent
      v-if="showBranches"
      :client="client"
      :showAddBranchInGrid="showAddBranchInGrid"
      :showClientInGrid = "showClientInGrid"
      v-model="showBranches"
    />

    <ManageBranchComponent
      v-if="addingBranch"
      v-model="addingBranch"
      :client="client"
      @branch-saved="refresh"
    />


  </div>
</template>

<script>

import BranchesGridComponent from '~/components/branches/BranchesGrid'
import ManageBranchComponent from '~/components/branches/ManageBranch'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    BranchesGridComponent,
    ManageBranchComponent
  },
  data: () => ({
    addingBranch: false,
    showBranches: true,
    showAddBranchInGrid: false,
    showClientInGrid: true,
    client: null,
    pCreateEditDelete: false
  }),
  created () {
    if (this.$laravel.hasPermission('manage any branch')) {
      this.pCreateEditDelete = true
    }
  },
  methods: {
    addBranch () {
      this.addingBranch = true
    },
    refresh () {
      this.showBranches = false
      this.$nextTick().then(() => {
        // Add the component back in
        this.showBranches = true
      });
    }
  }
}
</script>
