import swal from 'sweetalert2'

const Cookie = process.client ? require('js-cookie') : undefined

process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0'

export default ({ $axios, app, store, redirect }) => {
  // Request interceptor
  $axios.interceptors.request.use((request) => {
    request.baseURL = process.env.apiUrl

    const token = store.getters.token

    if (token) {
      request.headers.common.Authorization = `Bearer ${token}`
    } else {
      request.headers.common.Authorization = null
    }

    return request
  })

  // Response interceptor
  $axios.interceptors.response.use(response => response, async (error) => {
    const { status } = error.response || {}

    // if (status === 500 && !process.server && -1 !== error.response.url.indexOf('api/auth/refresh')) {
    //   redirect(302, '/login')
    // }

    // if (status >= 500 && !process.server) {
    //   await swal.fire({
    //     type: 'error',
    //     title: 'Oops...',
    //     text: 'Something went wrong! Please try again.',
    //     reverseButtons: true,
    //     confirmButtonText: 'Ok',
    //     cancelButtonText: 'Cancel'
    //   })
    // }

    if (status === 401) {
      // if (!process.server) {
      //   await swal.fire({
      //     type: 'warning',
      //     title: 'Unauthorized!',
      //     text: 'Please ensure you have permission to perform the given action and that you\'re sessions is still logged in. If the issue persists then please refresh your page.',
      //     reverseButtons: true,
      //     confirmButtonText: 'Ok',
      //     cancelButtonText: 'Cancel'
      //   })
      // }

      redirect(302, '/login')

      return Promise.reject(error)
    }

    if (status === 422) {
      error.response.parsedErrors = []
      const errors = error.response.data.errors
      Object.keys(errors).forEach((key) => {
        errors[key].forEach((serverError) => {
          error.response.parsedErrors.push({
            field: key,
            msg: serverError
          })
        })
      })
    }

    return Promise.reject(error)
  })
}
