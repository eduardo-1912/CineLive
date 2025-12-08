package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import android.content.Intent;
import android.os.Bundle;

import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import java.util.Arrays;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.activities.DetalhesCompraActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.DetalhesFilmeActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.CinemasAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.ComprasAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentComprasBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ComprasListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.ComprasManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;

public class ComprasFragment extends Fragment {
    private FragmentComprasBinding binding;
    private ComprasManager comprasManager;
    private ComprasAdapter adapter;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        comprasManager = ComprasManager.getInstance();
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentComprasBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {

        binding.rvCompras.setLayoutManager(new LinearLayoutManager(getContext()));

        loadCompras();
    }

    private void loadCompras() {
        comprasManager.fetchCompras(getContext(), new ComprasListener() {
            @Override
            public void onSuccess(List<Compra> compras) {
                setList(compras);
            }

            @Override
            public void onEmpty() {

            }

            @Override
            public void onError() {

            }
        });
    }

    private void setList(List<Compra> compras) {
        adapter = new ComprasAdapter(compras, compra -> {
            Intent intent = new Intent(getActivity(), DetalhesCompraActivity.class);
            intent.putExtra("id", compra.getId());
            startActivity(intent);
        });

        binding.rvCompras.setAdapter(adapter);
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}