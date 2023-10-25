export const state = () => ({
  applicationTitle: null,
  selectedTeam: null,
  isTeamMode: false,
  isAdminMode: false,
  drawer: true
})

export const mutations = {
  applicationTitle (state, title) {
    state.applicationTitle = title
  },
  toggleDrawer (state) {
    state.drawer = !state.drawer
  }
}

export const getters = {
  DRAWER_STATE (state) {
    return state.drawer
  }
}

export const actions = {
  nuxtServerInit ({ commit }, { req }) {
  },
  TOGGLE_DRAWER ({ commit }) {
    commit('toggleDrawer')
  },
}
