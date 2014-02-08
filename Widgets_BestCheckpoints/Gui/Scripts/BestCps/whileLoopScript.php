foreach (Player in Players) {

    //If first checkpoint time or new checkpoint time
	if (!playerCheckPoint.existskey(Player.Login)){
		playerCheckPoint[Player.Login] = -1;
	}
		
	
	if(playerCheckPoint[Player.Login] != Player.CurRace.Checkpoints.count) {
        
		//Update the current checkpoint of this user
		playerCheckPoint[Player.Login] = Player.CurRace.Checkpoints.count;
		declare curCp = Player.CurRace.Checkpoints.count;
		declare cpIndex = (curCp % totalCp)-1;
        declare Integer lastCpIndex = totalCp - 1;
        declare time = 0;
        
         if(curCp > totalCp){
            time = Player.CurRace.Checkpoints[curCp-1] - Player.CurRace.Checkpoints[lastCpIndex];
        }else if(curCp > 0){
            time = Player.CurRace.Checkpoints[curCp-1];
        }
        
		//Check if better
        if(cpIndex >= 0 && (cpTimes[cpIndex] > time || cpTimes[cpIndex] == 0)){
            needUpdate = True;
            cpTimes[cpIndex] = time;
            
            declare nickLabel = (Page.GetFirstChild("CpNick_"^cpIndex) as CMlLabel);
			declare timeLabel = (Page.GetFirstChild("CpTime"^cpIndex) as CMlLabel);
            
            
			if(nickLabel != Null){		
			    nickLabel.SetText(Player.Name);
			    timeLabel.SetText("$ff0" ^ (cpIndex + 1 ) ^ " $fff" ^ TimeToText(cpTimes[cpIndex]) );
			}
        }
	}
}
