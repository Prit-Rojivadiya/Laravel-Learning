<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Repair Order Type</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editRepairOrderType">Edit</v-btn>
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
        {{ repair_order_type.name }}
      </v-card-title>
      <v-card-text>
        <RepairOrderTypeOverviewComponent
          v-if="!loading"
          :repair_order_type="repair_order_type"
          v-model="repair_order_type"
        />
      </v-card-text>
    </v-card>

    <ManageRepairOrderTypeComponent
      v-if="showEditRepairOrderType"
      :repair_order_type="repair_order_type"
      v-model="showEditRepairOrderType"
      @repair_order_type-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import RepairOrderTypeOverviewComponent from '~/components/repair_order_types/Overview'
import ManageRepairOrderTypeComponent from '~/components/repair_order_types/ManageRepairOrderType'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    RepairOrderTypeOverviewComponent,
    ManageRepairOrderTypeComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditRepairOrderType: false,
    repair_order_type: {
      name: null,
      code: null,
      desc: null,
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.repair_order_type = await this.$axios.$get(`repair_order_types/${this.$route.params.id}`)
      this.loading = false
    },
    editRepairOrderType () {
      this.showEditRepairOrderType = true
    },
    async deleteItem () {
      await this.$axios.$delete(`repair_order_types/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
