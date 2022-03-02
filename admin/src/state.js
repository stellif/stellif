import { createGlobalState } from "react-hooks-global-state";

const initialState = {
  token: false,
};

const { useGlobalState } = createGlobalState(initialState);

export default useGlobalState;
