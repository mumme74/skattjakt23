import  React, { useReducer } from "react";
import { Group, appReducer, appState } from "../../state/reducers";
import { useCustomContext } from "../../state/custom.context";


function TrackGroup(props: {group:Group}) {

  const answersFor = (member:string) => {
    return props.group.answers.reduce((p,a)=>{
      return a.answeredBy=== member ? p+1 : p;
    }, 0)
  }

  const pointsFor = (member:string) => {
    return props.group.answers.reduce((p,a)=>{
      return a.answeredBy === member ? p+a.points : p;
    }, 0);
  }

  return <div className='bg-gray border-1'>
    <h4>{props.group.name}</h4>
    {props.group.members.map(member=>
      <div>
        <span>{member}</span>
        answers:{answersFor(member)}
        points: {pointsFor(member)}
      </div>
    )}
  </div>
}


export function TrackProgress() {

  const {state, dispatch} = useCustomContext();
  const {groups} = state;

  return <>
    <h1>Följ upp hur det går för övriga.</h1>
    <div className="r2 bg-gray">
      {groups.map(group=>
        <div>
          <h5>{group.name}</h5>
          <TrackGroup group={group} />
        </div>
      )}
    </div>
  </>
}