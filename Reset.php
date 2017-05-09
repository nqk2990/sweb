<?
	if(!$AccessModule)die();		
	include('config/config_reset.ini');
	include('config/config_resetvip.ini');
	include('config/config_uythac_reset.ini');
	include('config/config_uythac_resetvip.ini');
	include('config/config_relife.ini');
	include('config/config_gioihanrs.ini');
	include('config/config_diemdanhvong.ini');
	include('config/config_guild.ini');
	include('config/config_eventghrsgoldhour.ini');
	$CheckDay=date('D');
	$hour_check=date("H");
	$minute_check=date("i");

	//Lay thong tin cua nhan vat
	$SQL_GetReset=mssql_query("select Resets,Clevel,Last_Reset,Point_UyThac,Day_Reset,Class,Relifes,Point_Event_Add,LevelUpPoint,Leadership,PointDuTru,DanhVong_Day,DiemDanhVong,GHRSExtended,CharGHRSGold from character where name='$Name' AND AccountID='$Accountid'");
	$InfoChar=mssql_fetch_row($SQL_GetReset);
	//Lay thong tin dieu kien reset de so sanh
	$SQL_GetAccInfo=mssql_query("select Bank_MoneyC1,Bank_MoneyC2,Bank_Zen,Bank_Chaos,Bank_Cre,Bank_Blue,Bank_MoneyC3 from memb_info where memb___id='$Accountid'");
	$InfoAcc=mssql_fetch_row($SQL_GetAccInfo);
	$LevelDanhVong  = $InfoChar[1];
	$ResetNext=$InfoChar[0]+1;
	$ResetInDayNext=$InfoChar[4]+1;
	$RelifeNext=$InfoChar[6]+1;
	$RelifeCurrent=$InfoChar[6];
	if($Use_HoTroGuild){
		//Check Nhan Vat co thuoc Guild hay khong.
		$Query_CheckInGuild = "select G_Name,G_Status from GuildMember where Name ='$Name'";
		$Result_Query = mssql_query($Query_CheckInGuild) or die("Loi: $Query_CheckInGuild");
		$Row_Query = mssql_num_rows($Result_Query);
		if($Row_Query>0){
			$InGuild = mssql_fetch_row($Result_Query);
			$CheckInTop = CheckInTopGuild($InGuild[0]);
			$TopGuild = $CheckInTop['InTop'];
			$LevelGuild = $level_guild[$TopGuild];
			$PointGuild = $point_guild[$TopGuild];
		}
	}
	//Neu dat cap do Reset de Relife se hien len thong bao Relife
	if($ResetNext>$rl_reset_relife[$RelifeNext]){
		$Content= "Bạn hãy chuyển sang trang Relife để Relife.";
	}
	else
	{			
		$Reset_Temp=0;
		$Point_Thuong=0;
		$Point_Vip=0;
		$MenhLenh_Thuong=0;
		$MenhLenh_Vip=0;
		$Captt=1;
		$CapCurrent=0;
		$CapResetLimit=0;
			
		//Xac dinh cap reset trong bang reset
		for($i=0;$i<$cap_reset_max;$i++)
		{
			if($ResetNext>$reset_cap[$i] && $ResetNext<=$reset_cap[$i+1]){
				$CapCurrent=$i+1;break;
			}	
		}
		//End xac dinh cap RS
					
		//Tinh Point sau khi reset
		while($Captt<=$CapCurrent)
		{
			for($x=$reset_cap[$Captt-1];$x<$reset_cap[$Captt];$x++)
			{
				if($Reset_Temp>=$ResetNext) break;
				
				$Point_Thuong+=$point_cap[$Captt];
				$Point_Vip+=$point_vip_cap[$Captt];
				$MenhLenh_Thuong+=$ml_cap[$Captt];
				$MenhLenh_Vip+=$ml_vip_cap[$Captt];
				$Reset_Temp++;
			}
			$Captt++;
		}
		$Point_Thuong+=$rl_point_relife[$RelifeCurrent];
		$Point_Vip+=$rl_point_relife[$RelifeCurrent];
		$MenhLenh_Thuong+=$rl_ml_relife[$RelifeCurrent];
		$MenhLenh_Vip+=$rl_ml_relife[$RelifeCurrent];
		//End tinh Point

		//Xac dinh vi tri TOP Reset
		$TopGH=file('LogSQL/TopGHanReset.txt');
		$ListCharGH=explode('|',$TopGH[0]);
		$TOP_Reset=array_search($Name,$ListCharGH);
		if(!$TOP_Reset)$TOP_Reset=$gioihanrs_top[count($gioihanrs_top)-2]+1;
		//--

	//-------Reset diem uy thac auto
	
	//Reset ut thuong
	$Point_UTTemp=$InfoChar[3];
	$Reset_TempUT=$InfoChar[0];
	$ZenCheckDu = $InfoAcc[2];
	$ResetUTCheck=0;
	$CapRs_UTTemp=1;
	$PointUTDu=0;
	$YeuCau_Zen=0;
	$YeuCau_Chaos=0;
	$YeuCau_Cre=0;
	$YeuCau_Blue=0;
	$Point_Nhan=0;
	$MenhLenh_Nhan=0;
	
	
	while($Point_UTTemp>=0 && $Reset_TempUT<$rl_reset_relife[$RelifeNext] && $ZenCheckDu>=0)
	{
		for($f=$reset_cap[$CapRs_UTTemp-1];$f<$reset_cap[$CapRs_UTTemp];$f++)
		{
			if($ResetUTCheck>$Reset_TempUT)	
			{
				$Point_UTTemp-=$point_uythac_rs_cap[$CapRs_UTTemp];
				$PointUTDu=$Point_UTTemp+$point_uythac_rs_cap[$CapRs_UTTemp];
				$ZenCheckDu -= $zen_cap[$CapRs_UTTemp];
				
				if($Point_UTTemp<0 || $Reset_TempUT>=$rl_reset_relife[$RelifeNext] || $ZenCheckDu < 0){
					break;
				}
				else{
					$YeuCau_Zen+=$zen_cap[$CapRs_UTTemp];
					$YeuCau_Chaos+=$chao_cap[$CapRs_UTTemp];
					$YeuCau_Cre+=$cre_cap[$CapRs_UTTemp];
					$YeuCau_Blue+=$blue_cap[$CapRs_UTTemp];
					$Point_Nhan+=$point_cap[$CapRs_UTTemp];
					$MenhLenh_Nhan+=$ml_cap[$CapRs_UTTemp];
					$Reset_TempUT++;
				}
			}
			$ResetUTCheck++;
		}
		$CapRs_UTTemp++;
	}
	
	//Reset ut vip
	if($_GET['ResetType']=='UTVIPMoney1') $MoneyCheckDu=$InfoAcc[0];
	elseif($_GET['ResetType']=='UTVIPMoney2') $MoneyCheckDu=$InfoAcc[1];
	
	$Point_UTTempVIP=$InfoChar[3];
	$Reset_TempUTVIP=$InfoChar[0];
	$ResetUTCheckVIP=0;
	$CapRs_UTTempVIP=1;
	$PointUTDuVIP=0;
	$YeuCau_Money=0;
	$Point_NhanVIP=0;
	$MenhLenh_NhanVIP=0;	
	
	while($Point_UTTempVIP>=0 && $Reset_TempUTVIP<$rl_reset_relife[$RelifeNext] && $MoneyCheckDu>=0)
	{
		for($z=$reset_cap[$CapRs_UTTempVIP-1];$z<$reset_cap[$CapRs_UTTempVIP];$z++)
		{
			if($ResetUTCheckVIP>$Reset_TempUTVIP)	
			{
				$Point_UTTempVIP-=$point_uythac_rsvip_cap[$CapRs_UTTempVIP];
				$PointUTDuVIP=$Point_UTTempVIP+$point_uythac_rsvip_cap[$CapRs_UTTempVIP];
				
				if($_GET['ResetType']=='UTVIPMoney1') $MoneyCheckDu-=$money1_vip_cap[$CapRs_UTTempVIP];
				elseif($_GET['ResetType']=='UTVIPMoney2') $MoneyCheckDu-=floor($money1_vip_cap[$CapRs_UTTempVIP]*(1+$moneycap2_extra/100));

				if($Point_UTTempVIP<0 || $Reset_TempUTVIP>=$rl_reset_relife[$RelifeNext] || $MoneyCheckDu<0){
					break;
				}
				else{
					$YeuCau_Money+=$money1_vip_cap[$CapRs_UTTempVIP];
					$Point_NhanVIP+=$point_vip_cap[$CapRs_UTTempVIP];
					$MenhLenh_NhanVIP+=$ml_vip_cap[$CapRs_UTTempVIP];
					$Reset_TempUTVIP++;
				}
			}
			$ResetUTCheckVIP++;
		}
		$CapRs_UTTempVIP++;
	}
	//End
					
		//Xac dinh cap gioi han reset trong TOP
		for($j=0;$j<$cap_gioihanrs_max;$j++)
		{
			if($gioihanrs_top[$j+1]=='>') {$CapResetLimit=$cap_gioihanrs_max;break;}
			else{
				if($TOP_Reset>$gioihanrs_top[$j] && $TOP_Reset<=$gioihanrs_top[$j+1]){
					$CapResetLimit=$j+1;break;
				}
			}
		}		
		//End xac dinh cap gh
			
		//Giam Level cho tung Class
		if ($InfoChar[5] == $class_dk_1 OR $InfoChar[5] == $class_dk_2 OR $InfoChar[5] == $class_dk_3) $GiamLevel=$giamlevel_dk;
		elseif ($InfoChar[5] == $class_mg_2 OR $InfoChar[5] == $class_mg_3) $GiamLevel=$giamlevel_mg;
		elseif ($InfoChar[5] == $class_elf_1 OR $InfoChar[5] == $class_elf_2 OR $InfoChar[5] == $class_elf_3) $GiamLevel=$giamlevel_elf;
		elseif ($InfoChar[5] == $class_dl_2 OR $InfoChar[5] == $class_dl_3) $GiamLevel=$giamlevel_dl;
		elseif ($InfoChar[5] == $class_sum_1 OR $InfoChar[5] == $class_sum_2 OR $InfoChar[5] == $class_sum_3) $GiamLevel=$giamlevel_sum;
		elseif ($InfoChar[5] == $class_rf_2 OR $InfoChar[5] == $class_rf_3) $GiamLevel=$giamlevel_rg;	
		
		//End giam level
			
		//Begin hỗ trợ tân thủ
		
		//Tinh tong so lan reset theo cap Relife
		$TotalReset=0;
		for($i=1;$i<=$RelifeCurrent;$i++){$TotalReset=$TotalReset+$rl_reset_relife[$i];}
		$TotalReset=$TotalReset+$ResetNext;
		//End tinh tong reset
		
		$GiamLevelTT=0;
		if ($Use_HoTroTanThuReset) {
			include('config/config_hotrotanthureset.ini');
			for($i=1;$i<=$capsudung;$i++){
				if($TTcap_reset_min[$i] <= $TotalReset && $TotalReset <= $TTcap_reset_max[$i]){
					$GiamLevelTT = $TTcap_levelgiam[$i];
					break;
				}
			}
		}
		//End hỗ trợ tân thủ
					
		$Req_Level=0;
		$Req_Zen=0;
		$Req_Time=0;
		$Req_Chaos=0;
		$Req_Creation=0;
		$Req_Blue=0;
		$Req_Money1=0;
		$Req_Money2=0;
		$Req_Point_UyThac=0;
		$Point=0;
		$MenhLenh=0;
								
		//Tinh toan tung type reset
		switch($_GET['ResetType'])
		{
			//Reset thuong
			case 'Thuong':
				$Req_Level=$level_cap[$CapCurrent]-$GiamLevel-$GiamLevelTT-$LevelGuild;
				$Req_Zen=$zen_cap[$CapCurrent];
				$Req_Time=$InfoChar[2]+($time_reset_next[$CapCurrent]*60);
				$Req_Chaos=$chao_cap[$CapCurrent];
				$Req_Creation=$cre_cap[$CapCurrent];
				$Req_Blue=$blue_cap[$CapCurrent];
				$Point=$Point_Thuong;
				$MenhLenh=$MenhLenh_Thuong;
				break;
			//Reset vip VCent
			case 'VIPMoney1':
				if(!$Use_ResetVIP) {echo "<Response>Chức năng này không được sử dụng</Response>".$ContentUpdate;exit();}
				$Req_Level=$level_vip_cap[$CapCurrent]-$GiamLevel-$GiamLevelTT-$LevelGuild;
				$Req_Time=$InfoChar[2]+($time_reset_next[$CapCurrent]*60);
				$Req_Money1=$money1_vip_cap[$CapCurrent];
				$Point=$Point_Vip;
				$MenhLenh=$MenhLenh_Vip;
				break;
			//Reset vip Gcent
			case 'VIPMoney2':
				if(!$Use_ResetVIP) {echo "<Response>Chức năng này không được sử dụng</Response>".$ContentUpdate;exit();}
				$Req_Level=$level_vip_cap[$CapCurrent]-$GiamLevel-$GiamLevelTT-$LevelGuild;
				$Req_Time=$InfoChar[2]+($time_reset_next[$CapCurrent]*60);
				$Req_Money2=floor($money1_vip_cap[$CapCurrent]*(1+$moneycap2_extra/100));
				$Point=$Point_Vip;
				$MenhLenh=$MenhLenh_Vip;
				break;
			//Reset Uy thac thuong
			case 'UTThuong':
				$Req_Zen=$YeuCau_Zen;
				$Req_Chaos=$YeuCau_Chaos;
				$Req_Creation=$YeuCau_Cre;
				$Req_Blue=$YeuCau_Blue;
				$Req_Point_UyThac=$InfoChar[3]-$PointUTDu;
				$Point=$InfoChar[10]+$Point_Nhan;
				$MenhLenh=$InfoChar[9]+$MenhLenh_Nhan;
				$ResetNext=$Reset_TempUT;
				break;
			//Reset Uy thac VIP Gcent
			case 'UTVIPMoney1':
				if(!$Use_ResetUTVIP) {echo "<Response>Chức năng này không được sử dụng</Response>".$ContentUpdate;exit();}
				$Req_Money1=$YeuCau_Money;
				$Req_Point_UyThac=$InfoChar[3]-$PointUTDuVIP;
				$Point=$InfoChar[10]+$Point_NhanVIP;
				$MenhLenh=$InfoChar[9]+$MenhLenh_NhanVIP;
				$ResetNext=$Reset_TempUTVIP;
				break;
			//Reset Uy thac VIP Vcent
			case 'UTVIPMoney2':
				if(!$Use_ResetUTVIP) {echo "<Response>Chức năng này không được sử dụng</Response>".$ContentUpdate;exit();}
				$Req_Money2=floor($YeuCau_Money*(1+$moneycap2_extra/100));
				$Req_Point_UyThac=$InfoChar[3]-$PointUTDuVIP;
				$Point=$InfoChar[10]+$Point_NhanVIP;
				$MenhLenh=$InfoChar[9]+$MenhLenh_NhanVIP;
				$ResetNext=$Reset_TempUTVIP;
				break;
			default:
				exit("<Response>Yêu cầu không hợp lệ</Response>".$ContentUpdate);
		}
		//Tang Point Guild 
		$Point = $Point + (($Point*$PointGuild)/100);
		//Event Top Reset Mini Check Start
		$EventTopRsMini_Start = false;
		if($CheckDay == $TopResetMini_DayNotLimit && $Event_TopRsMini_on) if($hour_check >= $TopResetMini_HourStart && $minute_check >= $TopResetMini_MinuteStart) $EventTopRsMini_Start=true;	
						
		//Thiet dat diem Menh lenh cho DL
		if($menhlenh_dl>0) $MenhLenh=$menhlenh_dl;
		
		if($Use_HoTroGuild){
			if($CheckInTop['GUnion']){
				$Req_Level+= $LeagueCommunityLevel;
			}
			if($CheckInTop['GuildMem'] > $GuildMem){
				$Req_Level+= $GuildCommunityLevel;
			}
		}
		
		if(strstr($_GET['ResetType'],'Money1')){
			$Req_Money3 = floor($Req_Money1*(1+($money3_etra/100)));
		}
		if(($InfoAcc[6]>$Req_Money3) && isset($Req_Money3)){
			$Money1_KiemTra=$InfoAcc[6]-$Req_Money3;
			$name_money = $name_money_cap3;
			$File_Money = 'Bank_MoneyC3';
			$ContenFixMoney = false;
		}else{
			$Money1_KiemTra=$InfoAcc[0]-$Req_Money1;
			$name_money = $name_money_cap1;
			$File_Money = 'Bank_MoneyC1';
			$ContenFixMoney = true;
		}
		$DateGHRSALLStart = $DateopenBetaStart." ".$HouropenBetaStart.":00:00";
		$DateGHRSALLEnd = $DateopenBetaEnd." ".$HouropenBetaEnd.":59:00";
		if(($timestamp>strtotime($DateGHRSALLStart)) && ($timestamp<strtotime($DateGHRSALLEnd))){
			$gioihanrs_max_top[$CapResetLimit] = $GHRSAllTop;
		}
		//Kiem tra xem Co Nam Trong GHRS gio vag ko
		$TOPReset_KiemTraTemp=($gioihanrs_max_top[$CapResetLimit] + $InfoChar[13]) - $ResetInDayNext;
		if($Event_GHRSGoldHour && $TOPReset_KiemTraTemp<0){
			$HourServerTime = date("H",$timestamp);
			$GHRSGoldExtraGHRS = CalGioGHRSGold($HourServerTime);
			$GHRSGoldExtra = $GHRSGoldExtraGHRS['GHRS'];
			$GHRSGoldExtraMoney = $GHRSGoldExtraGHRS['Money'];
		}
		//!strstr($_GET[ResetType],'UT')
		//End
		$Money2_KiemTra=$InfoAcc[1]-$Req_Money2;
		$TOPReset_KiemTra=($gioihanrs_max_top[$CapResetLimit] + $InfoChar[13] + $GHRSGoldExtra) - $ResetInDayNext;
		$GHRSGOLDSQL = "CharGHRSGold=CharGHRSGold+0,";
		if($GHRSGoldExtraGHRS['Time'] && ($InfoChar[14]<=$GHRSGoldExtra)){
			$Money2_KiemTra=$InfoAcc[1]-$GHRSGoldExtraMoney;
			$GHRSGOLDSQL = "CharGHRSGold=CharGHRSGold+1,";
		}
		$Level_KiemTra=$InfoChar[1]-$Req_Level;
		$Zen_KiemTra=number_format(($InfoAcc[2]-$Req_Zen),0,',','');
		$Chaos_KiemTra=$InfoAcc[3]-$Req_Chaos;
		$Creation_KiemTra=$InfoAcc[4]-$Req_Creation;
		$Blue_KiemTra=$InfoAcc[5]-$Req_Blue;
		$Time_KiemTra=$timestamp-$Req_Time;
		$PointUT_KiemTra=$InfoChar[3]-$Req_Point_UyThac;
		if($Money1_KiemTra<0) {$Content= "Bạn còn thiếu ".($Money1_KiemTra*-1)." $name_money";}
		elseif($Money2_KiemTra<0){$Content= "Bạn còn thiếu ".($Money2_KiemTra*-1)." $name_money_cap2";}
		elseif($Level_KiemTra<0){$Content= "Bạn còn thiếu ".($Level_KiemTra*-1)." Level";}
		elseif($Zen_KiemTra<0){$Content= "Bạn còn thiếu ".number_format($Zen_KiemTra*-1)." Zen";}
		elseif($Chaos_KiemTra<0){$Content= "Bạn còn thiếu ".($Chaos_KiemTra*-1)." viên ngọc hỗn nguyên";}
		elseif($Creation_KiemTra<0){$Content= "Bạn còn thiếu ".($Creation_KiemTra*-1)." viên ngọc sáng tạo";}
		elseif($Blue_KiemTra<0){$Content= "Bạn còn thiếu ".($Blue_KiemTra*-1)." chiếc lông vũ";}
		elseif($PointUT_KiemTra<0){$Content= "Bạn còn thiếu ".($PointUT_KiemTra*-1)." điểm ủy thác";}
		elseif($Time_KiemTra<0 && $time_reset_next[$CapCurrent]>0 && !strstr($_GET[ResetType],'UT')){$Content= "Vui lòng chờ tới ".date('d/m/Y H:i:s',$Req_Time);}
		elseif($TOPReset_KiemTra<0 && $use_gioihanrs && !strstr($_GET[ResetType],'UT') && !$event_danhvong_on && !$EventTopRsMini_Start){$Content= "Bạn đã Reset đạt mốc giới hạn ".($gioihanrs_max_top[$CapResetLimit] + $InfoChar[13])." Reset/Ngày";}
		else
		{
			Check_Item_DupeChar($Name);
			//Kiem tra cua hang ca nhan co do hay ko
			$inventory_query = "SELECT CAST(CAST(Inventory AS varbinary($Char_Inventory_Length)) AS image) AS col FROM Character WHERE AccountID = '$Accountid' AND Name='$Name'";
			$inventory_result_sql = mssql_query($inventory_query);
			$inventory_result = mssql_fetch_row($inventory_result_sql);
			$inventory = $inventory_result[0];
			$inventory = bin2hex($inventory);
			$inventory3 = substr($inventory,$PersonalShop_StartLength,1024);
			$inventory3 = strtoupper($inventory3);
			$HomDoMoRong= substr($inventory,2432,2048);

			$no_item = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
			for ($i=0;$i<32;$i++) $shop_empty .= $no_item;
			if ($inventory3 != $shop_empty) { $Content= "Cửa hàng cá nhân có vật phẩm. Vui lòng bỏ item ra khỏi cửa hàng cá nhân để tránh bị mất đồ.";}
			elseif (strstr($HomDoMoRong,'0') && strstr($VersionServer,'Over')){ $Content= "Hòm đồ mở rộng có vật phẩm. Vui lòng bỏ item ra khỏi hòm đồ mở rộng tránh bị mất đồ.";}
			else{
				//DarkWizard
				if ($InfoChar[5] == $class_dw_1 OR $InfoChar[5] == $class_dw_2 OR $InfoChar[5] == $class_dw_3)
				{$Strength=18;$Dexterity=18;$Vitality=15;$Energy=30;$Life=60;$MaxLife=60;$Mana=60;$MaxMana=60;$MapNumber=0;$MapPosX=182;$MapPosY=128;$Mapdir=0;}
				//DarkKnight
				else if ($InfoChar[5] == $class_dk_1 OR $InfoChar[5] == $class_dk_2 OR $InfoChar[5] == $class_dk_3)
				{$Strength=28;$Dexterity=20;$Vitality=25;$Energy=10;$Life=110;$MaxLife=110;$Mana=20;$MaxMana=20;$MapNumber=0;$MapPosX=182;$MapPosY=128;$Mapdir=0;}
				//Elf
				elseif ($InfoChar[5] == $class_elf_1 OR $InfoChar[5] == $class_elf_2 OR $InfoChar[5] == $class_elf_3)
				{$Strength=22;$Dexterity=25;$Vitality=20;$Energy=15;$Life=80;$MaxLife=80;$Mana=30;$MaxMana=30;$MapNumber=3;$MapPosX=175;$MapPosY=100;$Mapdir=4;}
				//Magic
				else if ($InfoChar[5] == $class_mg_2 OR $InfoChar[5] == $class_mg_3)
				{$Strength=26;$Dexterity=26;$Vitality=26;$Energy=26;$Life=110;$MaxLife=110;$Mana=60;$MaxMana=60;$MapNumber=0;$MapPosX=182;$MapPosY=128;$Mapdir=0;}
				//DarkLord
			    else if ($InfoChar[5] == $class_dl_2 OR $InfoChar[5] == $class_dl_3)
				{$Strength=26;$Dexterity=26;$Vitality=20;$Energy=20;$Life=90;$MaxLife=90;$Mana=60;$MaxMana=60;$MapNumber=0;$MapPosX=182;$MapPosY=128;$Mapdir=0;}
				//Summoner
				else if ($InfoChar[5] == $class_sum_1 OR $InfoChar[5] == $class_sum_2 OR $InfoChar[5] == $class_sum_3)
				{$Strength=21;$Dexterity=21;$Vitality=18;$Energy=23;$Life=60;$MaxLife=60;$Mana=60;$MaxMana=60;$MapNumber=51;$MapPosX=53;$MapPosY=226;$Mapdir=0;}
				//RageFighter
				else if ($InfoChar[5] == $class_rf_2 OR $InfoChar[5] == $class_rf_3)
				{$Strength=32;$Dexterity=27;$Vitality=25;$Energy=20;$Life=60;$MaxLife=60;$Mana=60;$MaxMana=60;$MapNumber=51;$MapPosX=53;$MapPosY=226;$Mapdir=0;}
				
				//Fix MenhLenh
				if($MenhLenh>32000) $MenhLenh=32000;
				
				if ($InfoChar[5] == $class_dl_2 OR $InfoChar[5] == $class_dl_3)
				{$SQL_OtherClass_Script=",[Leadership]='$MenhLenh' where name='$Name' And Accountid='$Accountid'";}
				else{$SQL_OtherClass_Script=" where name='$Name' And Accountid='$Accountid'";}
				//Diem danh vong
				$DanhVongInDay=$InfoChar[11];
				$DanhVongTong=$InfoChar[12];
				$IsAdd_DanhVong=false;
				if($event_danhvong_on && !strstr($_GET[ResetType],'UT')){
					if($TOPReset_KiemTra < 0 && $use_gioihanrs && !$EventTopRsMini_Start){
						$ResetNext--;
						$ResetInDayNext--;							
					}
					if($TOPReset_KiemTra < 0 && $use_gioihanrs)
					{
						
						if($CheckDay != $daynotlimit){
							if($DanhVongInDay < $gioihandiemdanhvong) $IsAdd_DanhVong=true;
							else{
								if(!$EventTopRsMini_Start) die("<Response>Bạn đã đạt giới hạn ghi điểm danh vọng.</Response>".$ContentUpdate);
							}
						}
						else{	
							if($hour_check>=$hourstart_notlimit && $minute_check>=$minutestart_notlimit) $IsAdd_DanhVong=true;
							else{
								if($DanhVongInDay < $gioihandiemdanhvong) $IsAdd_DanhVong=true;
								else{
									if(!$EventTopRsMini_Start) die("<Response>Bạn phải chờ tới $hourstart_notlimit giờ $minutestart_notlimit phút mới có thể tiếp tục ghi điểm danh vọng.</Response>".$ContentUpdate);
								}
							}							
						}
						if($TotalReset<$ResetsDVMin || $InfoChar[1]<$LevelDVMin){
							echo "<Response>Nhân vật $name cần nhiều hơn $ResetsDVMin Resets hoặc Level lớn hơn $LevelDVMin để có thể tham gia Event.</Response>"; exit();
						}	
						if($IsAdd_DanhVong){
							$DanhVongInDay+=$LevelDanhVong ;
							$DanhVongTong+=$LevelDanhVong ;
						}
					}							
				}
				//End diem danh vong
				
				//Update character Reset
				$Point = floor($Point);
				if(!strstr($_GET[ResetType],'UT')){
					//Cộng thêm Point Event
					$Point=$Point+$InfoChar[7];
					$Query_TempupdateChar = "Update character set $GHRSGOLDSQL [DiemDanhVong]='$DanhVongTong',[DanhVong_Day]='$DanhVongInDay',[Point_UyThac]='$PointUT_KiemTra',[Day_Reset]='$ResetInDayNext',[Last_Reset]='$timestamp',[clevel]='1',[experience]='0',[LevelUpPoint]='0',[PointDuTru]='$Point',[Resets]='$ResetNext',[strength]='$Strength',[dexterity]='$Dexterity',[vitality]='$Vitality',[energy]='$Energy',[Life]='$Life',[MaxLife]='$MaxLife',[Mana]='$Mana',[MaxMana]='$MaxMana',[MapNumber]='$MapNumber',[MapPosX]='$MapPosX',[MapPosY]='$MapPosY',[MapDir]='$Mapdir'$SQL_OtherClass_Script";
					$SQL_Update_Character=mssql_query($Query_TempupdateChar) or die("Lỗi Query $Query_TempupdateChar");
					
					if(!$IsAdd_DanhVong && !$EventTopRsMini_Start)
					{
						//Kiem tra reset thang
						CheckResetMonth($Name);

						//Kiem tra reset tuan
						CheckResetWeek($Name);
						
						//Kiem tra reset trong 1 khoang toi gian
						CheckResetInTime($Name);
						
					}
					else
					{						
						//Kiem tra danh vong trong 1 khoang thoi gian
						CheckEventDanhVong($Name,$LevelDanhVong);
						
						//Top Reset Mini
						CheckEventTopResetMini($Name);
					}
				}else{
					$SQL_Update_Character=mssql_query("Update character set [PointDuTru]='$Point',[Point_UyThac]='$PointUT_KiemTra',[Last_Reset]='$timestamp',[Resets]='$ResetNext'$SQL_OtherClass_Script") or die("Lỗi Query SQL_Update_Character");
				}
				//Tru dieu kien Reset Acc
				$UpdateReqAccReset=mssql_query("Update memb_info set Bank_Zen='$Zen_KiemTra',$File_Money='$Money1_KiemTra',Bank_MoneyC2='$Money2_KiemTra',Bank_Chaos='$Chaos_KiemTra',Bank_Cre='$Creation_KiemTra',Bank_Blue='$Blue_KiemTra' where memb___id='$Accountid'");
				//End Tru dieu kien Reset
							
				WriteLog("Nhân vật <b>$Name</b> thuộc tài khoản <b>$Accountid</b> đã <font color=#FF0000>Reset</font> lần thứ <b>$ResetNext</b>, Trước Reset: <b>$InfoChar[0]</b> Reset . Relife: $InfoChar[6]. Reset ngày : $ResetInDayNext. $name_money_cap1 : $Money1_KiemTra, $name_money_cap2 : $Money2_KiemTra, ZEN : $Zen_KiemTra","Reset_".$_GET[ResetType]);
						
				$Content= "OKMEN|$Money1_KiemTra|$Money2_KiemTra|$Zen_KiemTra|$Chaos_KiemTra|$Creation_KiemTra|$Blue_KiemTra|$timestamp|$PointUT_KiemTra|$Point|$Strength|$Dexterity|$Vitality|$Energy|$MapNumber|$DanhVongInDay|$DanhVongTong|$ResetInDayNext|$ResetNext|$IsAdd_DanhVong|$TOPReset_KiemTra|$ContenFixMoney";
			}
		}
	}
	echo "<Response>".$Content."</Response>".$ContentUpdate;
?>