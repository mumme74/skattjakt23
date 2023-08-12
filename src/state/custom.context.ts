import React, {Dispatch} from 'react';
import {AppState, AppAction} from './reducers'

interface IContextProps {
  state: AppState;
  dispatch:Dispatch<AppAction>
}

export const CustomContext = React.createContext({} as IContextProps);

export function useCustomContext() {
  return React.useContext(CustomContext);
}
