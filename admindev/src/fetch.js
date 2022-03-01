import axios from "axios";

const api = window.stellif_api_url ?? "http://stellif.test/api";

export async function _get(path, params = {}) {
  return await axios({
    method: "get",
    url: `${api}/${path}`,
    params,
    headers: {
      "Content-Type": "application/json",
    },
  });
}

export async function _post(path, data = {}) {
  return await axios({
    method: "post",
    url: `${api}/${path}`,
    data,
    headers: {
      "Content-Type": "application/json",
    },
  });
}

export async function _delete(path, data = {}) {
  return await axios({
    method: "delete",
    url: `${api}/${path}`,
    data,
    headers: {
      "Content-Type": "application/json",
    },
  });
}
