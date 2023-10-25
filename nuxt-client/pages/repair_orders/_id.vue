<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Repair Order</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editRepairOrder">Edit</v-btn>
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


          <v-dialog
            v-model="deleteMediaDialog"
            persistent
            max-width="290"
          >
            <v-card>
              <v-card-title class="headline">Are you sure?</v-card-title>
              <v-card-text>Do you really want to delete?</v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="green darken-1" text :disabled="deletingLoading" @click="deleteDialog = false; deletingItem = null">Cancel</v-btn>
                <v-btn color="red" :loading="deletingLoading" @click="confirmedMediaDelete()">Delete</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-col>
      </v-row>
    </div>

    <v-card>
      <v-card-title>
        RO#: {{repair_order.ro_number}}  &nbsp;&nbsp;  {{ repair_order.desc }}
      </v-card-title>
      <v-card-text>
        <FleetBreadcrumbs
          v-if="!loading && showOverview"
          :fleetEntity="repair_order"
          :fleetEntityType="'RO'"
          style="padding: 0px"
        />
        <RepairOrderOverviewComponent
          v-if="!loading && showOverview"
          @uploadedfile="getDataFromApi"
          :repair_order="repair_order"
          :repairOrderTotalPriceUpdated="repairOrderTotalPriceUpdated"
          v-model="repair_order"
        />
      </v-card-text>
    </v-card>

    <ManageRepairOrderComponent
      v-if="showEditRepairOrder"
      :repair_order="repair_order"
      :vehicle="repair_order.vehicle"
      :allowVehicleAssignment="allowVehicleAssignment"
      v-model="showEditRepairOrder"
      @repair_order-saved="getDataFromApi"
    />

    <v-card class="mt-15">
      <v-row>
        <v-col cols="8">
          <v-card-title>
            Images
          </v-card-title>
        </v-col>
        <v-col>
          <v-file-input v-if="!uploadingFile"  v-model="file" label="Click here to select a file" />
          <v-progress-linear indeterminate height="25" v-if="uploadingFile">
            <template v-slot:default="{ value }">
              <strong>Uploading...</strong>
            </template>
          </v-progress-linear>
        </v-col>
      </v-row>

      <v-data-table
        :headers="headers"
        :loading="loading"
        :items="repair_order.images"
        item-key="id"
        :items-per-page="100"
        :footer-props="{ itemsPerPageOptions: [50, 100, 200, 500, -1] }"
        dense
        @update:options="getDataFromApi"
      >
        <template v-slot:item.preview="{ item, value }" style="width: 200px">
          <img height="150px" style="max-width: 200px" class="mb-5 mt-5" v-if="item.is_image" :src="item.url" />
        </template>
        <template v-slot:item.name="{ item, value }">
          <a :href="item.url" target="_blank">{{ value }}</a>
        </template>
        <template v-slot:item.actions="{ item }">
          <v-tooltip top>
            <template v-slot:activator="{ on, attrs }">
              <v-icon
                small
                class="mr-2"
                v-bind="attrs"
                v-on="on"
                @click="deleteMedia(item)"
              >
                mdi-trash-can
              </v-icon>
            </template>
            <span>Manage Fleet</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card>

    <LineItemsGridComponent
      v-if="showLineItems"
      :repair_order="repair_order"
      :showAddLineItemInGrid="showAddLineItemInGrid"
      :showRepairOrderInGrid="showRepairOrderInGrid"
      v-model="showLineItems"
      @line_item-saved="refreshTotalPrice"
      @refresh-total-price="refreshTotalPrice"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import RepairOrderOverviewComponent from '~/components/repair_orders/Overview'
import ManageRepairOrderComponent from '~/components/repair_orders/ManageRepairOrder'
import LineItemsGridComponent from '~/components/line_items/LineItemsGrid'
import FleetBreadcrumbs from '~/components/breadcrumbs/FleetBreadcrumb'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    RepairOrderOverviewComponent,
    ManageRepairOrderComponent,
    LineItemsGridComponent,
    FleetBreadcrumbs
  },
  computed: {
    headers() {
      let headers = []
      //headers.push({ text: 'Fleet ID', align: 'left', value: 'id' })
      headers.push({text: 'Preview', value: 'preview', width: '5%' })
      headers.push({text: 'Name', align: 'left', value: 'name'})
      headers.push({text: 'Actions', value: 'actions', sortable: false, width: '5%' })
      return headers
    }
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditRepairOrder: false,
    allowVehicleAssignment: false,
    showOverview: true,
    showLineItems: false,
    showAddLineItemInGrid: true,
    showRepairOrderInGrid: false,
    repairOrderTotalPriceUpdated: null,
    file: null,
    uploadingFile: false,
    snackbar: false,
    snackbarText: null,
    deleteMediaDialog: false,
    deletingItem: null,
    deletingLoading: false,
    repair_order: {
      desc: null,
    },
  }),
  watch: {
    file(newVal) {
      if (newVal) {
        this.uploadFile();
      }
    }
  },
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    deleteMedia(item) {
      this.deleteMediaDialog = true;
      this.deletingItem = item;
    },
    async confirmedMediaDelete () {
      this.deletingLoading = true

      await this.$axios.$delete(`uploads/${this.deletingItem.id}`)

      this.deleteMediaDialog = false
      this.deletingItem = null
      this.deletingLoading = false

      this.getDataFromApi()
    },
    async uploadFile () {
      this.uploadingFile = true

      const currentObj = this
      const config = {
        headers: {
          'content-type': 'multipart/form-data'
        }
      }

      // form data
      const formData = new FormData()
      formData.append('file', this.file)
      formData.append('repair_order_id', this.repair_order.id)
      formData.append('file_type', 'images')

      this.importDialogWorking = true

      // send upload request
      this.$axios.post('/uploads', formData, config)
        .then((response) => {
          console.log(response)

          currentObj.file = null

          this.getDataFromApi()

          this.uploadingFile = false

          this.snackbar = false
          this.snackbarText = 'Result file has been successfully imported!'
          this.snackbar = true
        })
        .catch(function (error) {
          currentObj.output = error;
          this.snackbar = false
          this.snackbarText = 'Failed to upload file. Please check again and please ensure you\re not uploading a large file.'
          this.snackbar = true

          this.uploadingFile = false
        })
        .finally(() => {
          this.importDialogWorking = false
          this.importDialog = false
        })
    },
    async getDataFromApi(options) {
      this.loading = true
      this.showLineItems = false
      this.repair_order = await this.$axios.$get(`repair_orders/${this.$route.params.id}`)
      this.loading = false
      this.showLineItems = true
    },
    editRepairOrder () {
      this.showEditRepairOrder = true
    },
    async refreshTotalPrice (lineItem) {
      let tRO = await this.$axios.$get(`repair_orders/${this.repair_order.id}`)
      this.repairOrderTotalPriceUpdated = tRO.total_price
      this.refreshOverview()
    },
    async deleteItem () {
      await this.$axios.$delete(`repair_orders/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    },
    refreshOverview () {
      this.showOverview = false
      this.$nextTick().then(() => {
        this.showOverview = true
      })
    }
  }
}
</script>
